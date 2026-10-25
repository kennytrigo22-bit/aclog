<?php

namespace Tests\Feature\Learner;

use App\Models\NotificationInterne;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Tests\TestCase;

class SupportAndNotificationsTest extends TestCase
{
    // ───────── Support ─────────

    public function test_support_index_lists_only_own_tickets(): void
    {
        $me = $this->apprenant();
        $this->ticket($me, ['sujet' => 'Mon sujet perso']);
        $this->ticket($this->apprenant(), ['sujet' => 'Sujet de quelqu un d autre']);
        $this->actingAs($me)->get('/espace/support')->assertOk()->assertSee('Mon sujet perso')->assertDontSee('Sujet de quelqu un d autre');
    }

    public function test_create_ticket_and_validation(): void
    {
        $me = $this->apprenant();
        $f = $this->formation();
        $this->actingAs($me)->post('/espace/support/ticket', ['sujet' => 'Probleme', 'message' => 'Aide', 'formation_id' => $f->id])->assertSessionHas('success');
        $this->assertDatabaseHas('tickets', ['user_id' => $me->id, 'sujet' => 'Probleme', 'statut' => 'ouvert']);

        $this->post('/espace/support/ticket', [])->assertSessionHasErrors(['sujet', 'message']);
        $this->post('/espace/support/ticket', ['sujet' => 'x', 'message' => 'y', 'formation_id' => 99999])->assertSessionHasErrors('formation_id');
        $this->post('/espace/support/ticket', ['sujet' => str_repeat('a', 256), 'message' => 'y'])->assertSessionHasErrors('sujet');
        $this->post('/espace/support/ticket', ['sujet' => 'x', 'message' => str_repeat('a', 5001)])->assertSessionHasErrors('message');
    }

    public function test_ticket_cannot_be_created_for_another_user_or_status_forced(): void
    {
        $me = $this->apprenant();
        $victim = $this->apprenant();
        $this->actingAs($me)->post('/espace/support/ticket', ['sujet' => 'S', 'message' => 'M', 'user_id' => $victim->id, 'statut' => 'ferme']);
        $t = Ticket::first();
        $this->assertSame($me->id, $t->user_id);
        $this->assertSame('ouvert', $t->statut);
    }

    public function test_reply_to_own_ticket(): void
    {
        $me = $this->apprenant();
        $t = $this->ticket($me);
        $this->actingAs($me)->post("/espace/support/ticket/{$t->id}/message", ['message' => 'Merci'])->assertSessionHas('success');
        $this->assertDatabaseHas('ticket_messages', ['ticket_id' => $t->id, 'user_id' => $me->id, 'is_admin' => 0, 'message' => 'Merci']);
        $this->post("/espace/support/ticket/{$t->id}/message", [])->assertSessionHasErrors('message');
    }

    public function test_cannot_reply_to_someone_elses_ticket(): void
    {
        $t = $this->ticket($this->apprenant());
        $this->actingAs($this->apprenant())->post("/espace/support/ticket/{$t->id}/message", ['message' => 'intrusion'])->assertForbidden();
        $this->assertSame(0, TicketMessage::count());
    }

    public function test_user_cannot_forge_admin_flag_on_reply(): void
    {
        $me = $this->apprenant();
        $t = $this->ticket($me);
        $this->actingAs($me)->post("/espace/support/ticket/{$t->id}/message", ['message' => 'x', 'is_admin' => 1]);
        $this->assertSame(0, (int) TicketMessage::first()->is_admin);
    }

    public function test_ticket_content_is_escaped_in_learner_and_admin_views(): void
    {
        $me = $this->apprenant();
        $payload = '<script>alert("xss")</script>';
        $t = $this->ticket($me, ['sujet' => $payload, 'message' => $payload]);
        TicketMessage::create(['ticket_id' => $t->id, 'user_id' => $me->id, 'message' => $payload, 'is_admin' => false]);

        $this->actingAs($me)->get('/espace/support')->assertOk()->assertDontSee($payload, false);
        $this->actingAs($this->admin())->get(route('admin.tickets.show', $t))->assertOk()->assertDontSee($payload, false);
        $this->get(route('admin.tickets.index'))->assertOk()->assertDontSee($payload, false);
    }

    public function test_closed_ticket_cannot_receive_new_learner_messages(): void
    {
        $me = $this->apprenant();
        $t = $this->ticket($me, ['statut' => 'ferme']);
        $res = $this->actingAs($me)->post("/espace/support/ticket/{$t->id}/message", ['message' => 'reouvre']);
        $this->assertSame(0, TicketMessage::count(), 'Un ticket ferme accepte encore des messages (SupportController::repondre).');
    }

    // ───────── Notifications ─────────

    private function notif($user, array $a = []): NotificationInterne
    {
        return NotificationInterne::create(array_merge([
            'user_id' => $user->id, 'type' => 't', 'titre' => 'Titre notif', 'message' => 'Message notif', 'is_read' => false, 'created_at' => now(),
        ], $a));
    }

    public function test_notifications_index_lists_only_own(): void
    {
        $me = $this->apprenant();
        $this->notif($me, ['titre' => 'Ma notif']);
        $this->notif($this->apprenant(), ['titre' => 'Notif etrangere']);
        $this->actingAs($me)->get('/notifications')->assertOk()->assertSee('Ma notif')->assertDontSee('Notif etrangere');
    }

    public function test_mark_one_notification_as_read(): void
    {
        $me = $this->apprenant();
        $n = $this->notif($me);
        $this->actingAs($me)->post("/notifications/{$n->id}/lire")->assertRedirect();
        $this->assertTrue($n->fresh()->is_read);
    }

    public function test_cannot_mark_other_users_notification(): void
    {
        $n = $this->notif($this->apprenant());
        $this->actingAs($this->apprenant())->post("/notifications/{$n->id}/lire")->assertForbidden();
        $this->assertFalse($n->fresh()->is_read);
    }

    public function test_mark_all_as_read_touches_only_own(): void
    {
        $me = $this->apprenant();
        $other = $this->apprenant();
        $this->notif($me);
        $this->notif($me);
        $theirs = $this->notif($other);
        $this->actingAs($me)->post('/notifications/tout-lire')->assertRedirect();
        $this->assertSame(0, NotificationInterne::where('user_id', $me->id)->where('is_read', false)->count());
        $this->assertFalse($theirs->fresh()->is_read);
    }

    public function test_notifications_require_login(): void
    {
        $this->get('/notifications')->assertRedirect(route('login'));
        $this->post('/notifications/tout-lire')->assertRedirect(route('login'));
    }
}
