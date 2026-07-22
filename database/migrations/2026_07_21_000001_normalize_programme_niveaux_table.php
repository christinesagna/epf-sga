<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS vue_programme_niveau_documents');

        Schema::create('niveaux', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->timestamps();
        });

        Schema::table('programme_niveaux', function (Blueprint $table) {
            $table->foreignId('niveau_id')
                ->nullable()
                ->after('programme_id')
                ->constrained('niveaux')
                ->restrictOnDelete();
        });

        $now = now();
        $niveauxExistants = DB::table('programme_niveaux')
            ->select(['code', 'libelle'])
            ->distinct()
            ->get();

        foreach ($niveauxExistants as $niveau) {
            DB::table('niveaux')->updateOrInsert(
                ['code' => $niveau->code],
                [
                    'libelle' => $niveau->libelle,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        DB::statement(<<<'SQL'
            UPDATE programme_niveaux AS pn
            INNER JOIN niveaux AS n ON n.code = pn.code
            SET pn.niveau_id = n.id
            SQL);

        if (DB::table('programme_niveaux')->whereNull('niveau_id')->exists()) {
            throw new RuntimeException('Certains niveaux de programme n’ont pas pu être normalisés.');
        }

        Schema::table('programme_niveaux', function (Blueprint $table) {
            // La clé étrangère programme_id doit conserver son propre index
            // avant la suppression de l'ancien index unique composite.
            $table->index('programme_id');
            $table->dropUnique(['programme_id', 'code']);
        });

        DB::statement('ALTER TABLE programme_niveaux MODIFY niveau_id BIGINT UNSIGNED NOT NULL');

        Schema::table('programme_niveaux', function (Blueprint $table) {
            $table->dropColumn(['code', 'libelle']);
            $table->unique(['programme_id', 'niveau_id'], 'programme_niveau_unique');
        });

        $this->createNormalizedView();
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS vue_programme_niveau_documents');

        Schema::table('programme_niveaux', function (Blueprint $table) {
            $table->string('code')->nullable()->after('niveau_id');
            $table->string('libelle')->nullable()->after('code');
        });

        DB::statement(<<<'SQL'
            UPDATE programme_niveaux AS pn
            INNER JOIN niveaux AS n ON n.id = pn.niveau_id
            SET pn.code = n.code, pn.libelle = n.libelle
            SQL);

        Schema::table('programme_niveaux', function (Blueprint $table) {
            $table->dropUnique('programme_niveau_unique');
        });

        DB::statement('ALTER TABLE programme_niveaux MODIFY code VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE programme_niveaux MODIFY libelle VARCHAR(255) NOT NULL');

        Schema::table('programme_niveaux', function (Blueprint $table) {
            $table->dropConstrainedForeignId('niveau_id');
            $table->unique(['programme_id', 'code']);
        });

        Schema::dropIfExists('niveaux');

        $this->createLegacyView();
    }

    private function createNormalizedView(): void
    {
        DB::statement(<<<'SQL'
            CREATE VIEW vue_programme_niveau_documents AS
            SELECT
                p.id AS programme_id,
                p.nom AS programme_nom,
                pn.id AS programme_niveau_id,
                n.code AS niveau_code,
                n.libelle AS niveau_libelle,
                td.id AS type_document_id,
                td.code AS document_code,
                td.libelle AS document_libelle,
                pntd.obligatoire,
                pntd.ordre
            FROM programme_niveau_type_document AS pntd
            INNER JOIN programme_niveaux AS pn
                ON pn.id = pntd.programme_niveau_id
            INNER JOIN niveaux AS n
                ON n.id = pn.niveau_id
            INNER JOIN programmes AS p
                ON p.id = pn.programme_id
            INNER JOIN types_documents AS td
                ON td.id = pntd.type_document_id
            SQL);
    }

    private function createLegacyView(): void
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
};
