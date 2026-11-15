<?php

namespace App\Modules;

use App\Modules\History\HistoryModule;
use App\Modules\Legal\LegalModule;
use App\Modules\Auth\AuthModule;
use Modularize\Module;

/** Agrupa los módulos de la aplicación. */
class AppModule extends Module
{
    /** Arranque de los módulos. */
    public function boot()
    {
        parent::boot();
    }
    /** Registra los módulos de auth, legal e historial. */
    public function register()
    {
        $this->provide(AuthModule::class);
        $this->provide(LegalModule::class);
        $this->provide(HistoryModule::class);
    }
}