{{#if editing}}
    <input value="{{name}}" type="text">
{{else}}
    {{#unless active}}
        <s>
    {{/unless}}
        <span>{{name}}</span>
    {{#unless active}}
        </s>
    {{/unless}}
    <span class="disable glyphicon glyphicon-{{#if active}}remove{{else}}ok{{/if}}"></span>
{{/if}}
