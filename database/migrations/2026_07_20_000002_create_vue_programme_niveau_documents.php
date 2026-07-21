<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE VIEW vue_programme_niveau_documents AS
            SELECT
                p.id AS programme_id,
                p.nom AS programme_nom,
                pn.id AS programme_niveau_id,
                pn.code AS niveau_code,
                pn.libelle AS niveau_libelle,
                td.id AS type_document_id,
                td.code AS document_code,
                td.libelle AS document_libelle,
                pntd.obligatoire,
                pntd.ordre
            FROM programme_niveau_type_document AS pntd
            INNER JOIN programme_niveaux AS pn
                ON pn.id = pntd.programme_niveau_id
            INNER JOIN programmes AS p
                ON p.id = pn.programme_id
            INNER JOIN types_documents AS td
                ON td.id = pntd.type_document_id
            SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS vue_programme_niveau_documents');
    }
};
