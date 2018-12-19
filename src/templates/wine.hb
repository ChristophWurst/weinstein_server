<td class="text-center">{{#if nr}}<a href="/wines/{{id}}">{{nr}}</a>{{else}}-{{/if}}</td>
{{#if show_catalogue_number }}
    <td class="text-center">
        {{#if catalogue_number}}
            {{ catalogue_number }}
        {{else}}
            -
        {{/if}}
    </td>{{/if}}
<td><a href="/settings/applicant/{{applicant.id}}">{{applicant.label}} {{applicant.lastname}}</a></td>
<td><a href="/settings/association/{{applicant.association.id}}">{{applicant.association.name}}</a></td>
<td>{{ label }}</td>
<td>{{ winesort.name }}</td>
<td>{{ vintage }}</td>
<td class="text-center">

    {{#if winequality}}{{ winequality.abbr }}{{else}}-{{/if}}
</td>
<td class="text-center">{{l10nFloat alcohol }}</td>
<td class="text-center">{{#if alcoholtot}}{{ l10nFloat alcoholtot }}{{else}}-{{/if}}</td>
<td class="text-center">{{l10nFloat sugar }}</td>
{{#if show_rating1 }}
    <td class="text-center">{{#if rating1}}{{l10nFloat rating1 }}{{else}}-{{/if}}</td>{{/if}}
{{#if show_rating2 }}
    <td class="text-center">{{#if rating2}}{{l10nFloat rating2 }}{{else}}-{{/if}}</td>{{/if}}
{{#if show_kdb }}
    <td class="text-center {{#if edit_kdb}}edit-kdb{{/if}}">
        {{#if kdb}}
            <span class="glyphicon glyphicon-ok"></span>
        {{else}}
            -
        {{/if}}
    </td>{{/if}}
{{#if show_excluded }}
    <td class="text-center {{#if edit_excluded}}edit-excluded{{/if}}">
        {{#if excluded}}
            <span class="glyphicon glyphicon-ok"></span>
        {{else}}
            -
        {{/if}}
    </td>{{/if}}
{{#if show_sosi }}
    <td class="text-center {{#if edit_sosi}}edit-sosi{{/if}}">
        {{#if sosi}}
            <span class="glyphicon glyphicon-ok"></span>
        {{else}}
            -
        {{/if}}
    </td>{{/if}}
{{#if show_chosen }}
    <td class="text-center {{#if canEditChosen}}edit-chosen{{/if}}">
        {{#if chosen}}
            <span class="glyphicon glyphicon-ok"></span>
        {{else}}
            -
        {{/if}}
    </td>{{/if}}
{{#if show_edit_wine }}
    <td>|</td>{{/if}}
{{#if show_enrollment_pdf_export}}
    <td class="text-center">

    </td>
{{/if}}
