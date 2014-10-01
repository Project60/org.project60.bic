{*-------------------------------------------------------+
| Project 60 - Little BIC extension                      |
| Copyright (C) 2014                                     |
| Author: B. Endres (endres -at- systopia.de)            |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+-------------------------------------------------------*}

{* TODO: make this more beatiful ;) *}
<table class="display dataTable" role="grid" border="0">
  <thead>
    <tr>
      <td>{ts}Country{/ts}</td>
      <td>{ts}Count{/ts}</td>
      <td></td>
    </tr>
  </thead>


  <tbody>
{foreach from=$countries item=country}
    <tr>
      <td>{$country}</td>
      <td>{$stats.$country}</td>
      <td>
        <div class="action-link">
          <a class="button crm-extensions-refresh" id="new" onClick="update('{$country}', this);">
            <span><div class="icon refresh-icon"></div>{ts}Update{/ts}</span>
          </a>
        </div>
      </td>
    </tr>
{/foreach}
  </tbody>


  <tfoot>
    <tr>
      <td></td>
      <td>{$total_count}</td>
      <td>
        <div class="action-link">
          <a class="button crm-extensions-refresh" id="new" onClick="update('all', this);">
            <span><div class="icon refresh-icon"></div>{ts}Update{/ts}</span>
          </a>
        </div>
      </td>
    </tr>
  </tfoot>
</table>


{literal}
<script type="text/javascript">
function update(country_code, button) {
  console.log('updating ' + country_code);
  cj(button).attr('disabled', 'disabled');
  CRM.api3('Bic', 'update', {"country": country_code}).done(
    function(result) {
      console.log('done');
    });
}
</script>
{/literal}