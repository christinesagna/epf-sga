<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE VIEW vue_programme_niveaux AS
            SELECT
                pn.id AS association_id,
                p.id AS programme_id,
                p.nom AS programme_nom,
                p.slug AS programme_slug,
                n.id AS niveau_id,
                n.code AS niveau_code,
                n.libelle AS niveau_libelle,
                pn.ordre,
                pn.actif
            FROM programme_niveaux AS pn
            INNER JOIN programmes AS p ON p.id = pn.programme_id
            INNER JOIN niveaux AS n ON n.id = pn.niveau_id
            SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS vue_programme_niveaux');
    }
};
