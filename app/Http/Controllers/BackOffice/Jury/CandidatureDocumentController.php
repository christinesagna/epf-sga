<?php

namespace App\Http\Controllers\BackOffice\Jury;

use App\Http\Controllers\Controller;
use App\Models\CandidatureDocument;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CandidatureDocumentController extends Controller
{
    public function show(CandidatureDocument $document): StreamedResponse
    {
        $document->loadMissing('candidature');

        Gate::authorize('viewJury', $document->candidature);

        $stockage = Storage::disk('local');

        abort_unless($stockage->exists($document->path), 404);

        $mimeType = $stockage->mimeType($document->path)
            ?: $document->mime_type
            ?: 'application/octet-stream';

        return $stockage->response(
            $document->path,
            $document->original_name,
            ['Content-Type' => $mimeType],
            'inline',
        );
    }
}
