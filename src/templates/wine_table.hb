<table class="table table-striped table-condensed wine-table">
    <thead>
    <tr>
        <th class="text-center">DateiNr</th>
        {{#if show_catalogue_number}}
            <th class="text-center">KatNr</th>{{/if}}
        <th>Betrieb</th>
        <th>Verein</th>
        <th>Marke</th>
        <th>Sorte</th>
        <th>Jahr</th>
        <th class="text-center">Qualit&auml;t</th>
        <th class="text-center">Alk.</th>
        <th class="text-center">Alk. ges.</th>
        <th class="text-center">Zucker</th>
        {{#if show_rating1 }}
            <th class="text-center">1. Bewertung</th>{{/if}}
        {{#if show_rating2 }}
            <th class="text-center">2. Bewertung</th>{{/if}}
        {{#if show_kdb }}
            <th class="text-center">KdB</th>{{/if}}
        {{#if show_excluded }}
            <th class="text-center">Ex</th>{{/if}}
        {{#if show_sosi }}
            <th class="text-center">SoSi</th>{{/if}}
        {{#if show_chosen }}
            <th class="text-center">Ausschank</th>{{/if}}
        {{#if show_edit_wine}}
            <th></th>{{/if}}
        {{#if show_enrollment_pdf_export}}
            <th class="text-center">Formular</th>
        {{/if}}
    </tr>
    </thead>
    <tbody id="wine_list">
    </tbody>
</table>
<div class="container-fluid">
    <div class="text-center">
        <button class="btn btn-primary wine-load-more" data-loading-text="Lade...">Mehr laden</button>
    </div>
</div>
