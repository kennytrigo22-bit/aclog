<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Formation extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre', 'slug', 'description', 'programme', 'categorie', 'niveau',
        'prix', 'prix_promo', 'promo_active', 'duree_heures', 'image',
        'video_intro', 'is_published', 'is_featured', 'badge', 'ordre',
    ];

    protected function casts(): array
    {
        return [
            'promo_active' => 'boolean',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Formation $f) {
            foreach ($f->modules()->with('quiz')->get() as $module) {
                $module->quiz?->delete();
            }
            $f->examens()->get()->each->delete();
            $f->ressources()->get()->each->delete();
        });
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('ordre');
    }

    public function ressources(): HasMany
    {
        return $this->hasMany(FormationRessource::class)->orderBy('ordre');
    }

    public function examens(): HasMany
    {
        return $this->hasMany(Examen::class);
    }

    public function examenTest(): HasOne
    {
        return $this->hasOne(Examen::class)->where('type', 'test');
    }

    public function examenFinal(): HasOne
    {
        return $this->hasOne(Examen::class)->where('type', 'final');
    }

    public function examen(): HasOne
    {
        return $this->hasOne(Examen::class)->where('type', 'final');
    }

    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class);
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    public function certificats(): HasMany
    {
        return $this->hasMany(Certificat::class);
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }

        $pool = [
            'logistique' => ['african-american-people-looking-supplies-list-pc-working-with-computer-check-inventory-logistics-employees-organizing-merchandise-shelves-warehouse-space-handheld-shot.jpg', 'global-logistics-transportation-network.jpg', '6206720.jpg'],
            'commerce' => ['aerial-view-cargo-ship-cargo-container-harbor (1).jpg', 'businessman-touching-virtual-screen.jpg', 'aerial-view-cargo-ship-cargo-container-harbor (2).jpg'],
            'douane' => ['industrial-port-container-yard.jpg', 'freight-trucks-driving-highway-logistics-road-transport-content.jpg', '6241196.jpg'],
        ][strtolower($this->categorie ?? '')] ?? ['global-logistics-transportation-network.jpg'];

        return asset('assets/images/thumbs/' . rawurlencode($pool[$this->id % count($pool)]));
    }

    public function getPrixEffectifAttribute(): int
    {
        return ($this->promo_active && $this->prix_promo) ? $this->prix_promo : $this->prix;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
