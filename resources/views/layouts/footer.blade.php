<footer class="mt-auto" style="background:#111827; color:#f9fafb; padding:24px 24px; margin:0; font-size:14px;">
    <div style="max-width:1200px; margin:0 auto; display:flex; flex-wrap:wrap; gap:24px; justify-content:space-between; align-items:flex-start; text-align:left;">
        <div style="flex:1 1 280px; min-width:220px;">
            <strong style="font-size:1rem; color:#9d174d; display:block; margin-bottom:10px;">EPF Africa</strong>
            <p style="margin:0; line-height:1.7; color:#cbd5e1;">École d'ingénierie dédiée à l'Afrique, offrant des formations modernes, un accompagnement personnalisé et une candidature 100% en ligne.</p>
        </div>
        <div style="flex:1 1 220px; min-width:220px;">
            <strong style="display:block; margin-bottom:10px; color:#1e3a8a;">Liens rapides</strong>
            <ul style="list-style:none; padding:0; margin:0; color:#cbd5e1; line-height:1.9;">
                <li><a href="{{ url('/') }}" style="color:#f8fafc; text-decoration:none;">Accueil</a></li>
                <li><a href="{{ route('programmes.index') }}" style="color:#f8fafc; text-decoration:none;">Programmes</a></li>
                <li><a href="{{ route('candidatures.create') }}" style="color:#f8fafc; text-decoration:none;">Candidature</a></li>
                
            </ul>
        </div>
        <div style="flex:1 1 220px; min-width:220px;">
            <strong style="display:block; margin-bottom:10px; color:#1e3a8a;">Contact</strong>
            <p style="margin:0; color:#cbd5e1; line-height:1.9;">
                Adresse : Avenue de l'Ingénieur, Dakar, Sénégal<br>
                Téléphone : +221 33 000 0000<br>
                Email : <a href="mailto:contact@epf-africa.com" style="color:#f8fafc; text-decoration:none;">contact@epf-africa.com</a>
            </p>
        </div>
    </div>
    <div style="margin-top:22px; border-top:1px solid rgba(157,23,77,0.2); padding-top:18px; text-align:center; color:#9ca3af; font-size:13px;">
        <p style="margin:0;">© {{ date('Y') }} EPF Africa. Tous droits réservés.</p>
    </div>
</footer>
