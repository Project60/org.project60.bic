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

<div id="help">{ts}
  If you click the Update button, next to the name of one of the listed countries,
  CiviCRM will try to connect to the official source of financial institutions
  in this country and retrieve its bank information. Then, you'll be able to
  query this information in the <a href="bicList">Banks List</a> page.
{/ts}</div>

{* TODO: make this more beatiful ;) *}
<table class="display" role="grid">
  <thead>
    <tr class="columnheader">
      <th>{ts}Country{/ts}</b></th>
      <th>{ts}Count{/ts}</b></th>
      <th>{ts}Actions{/ts}</th>
    </tr>
  </thead>

  <tbody>
{foreach from=$countries item=country}
    <tr class='{cycle values="odd-row,even-row"}'>
      <td>{$country_names.$country}</td>
      <td><div name='number'>{$stats.$country}</div><img name='busy' src="{$config->resourceBase}i/loading.gif" hidden="1"/></td>
      <td style="text-align:right">
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
    <tr class="mceLast">
      <td><b>{ts}Total{/ts}</b></td>
      <td><b><img name='busy' src="{$config->resourceBase}i/loading.gif" hidden="1"/><div name='number'>{$total_count}</div></b></td>
      <td>
        <div class="action-link">
          <a class="button crm-extensions-refresh" id="new" onClick="update('all', this);">
            <span><div class="icon refresh-icon"></div>{ts}Update All{/ts}</span>
          </a>
        </div>
      </td>
    </tr>
  </tfoot>
</table>

<div class="crm-accordion-wrapper open" id="test-search">
  <div class="crm-accordion-header">
    Find Banks
  </div>
  <div class="crm-accordion-body">
     <div class="crm-block crm-form-block crm-form-title-here-form-block">
       {include file="CRM/Bic/Page/BicList.tpl"}
     </div>
   </div>
</div>

{literal}
<script type="text/javascript">
  cj("#test-search .crm-accordion-header").click(showHideTestSearch);
  
  function showHideTestSearch() {
    cj("#test-search").toggleClass("open, collapsed");
  }

  cj("#printer-friendly").hide();
  cj("#access").hide();

  // UPDATE BUTTONS
  function update(country_code, button) {
    if (cj(button).hasClass('disabled')) {
      return;
    }

    // disable buttons
    cj('.button').addClass('disabled');
    
    if (country_code=='all') {
      // set ALL to busy
      cj(button).parent().parent().parent().parent().parent().find('[name="busy"]').show();
      cj(button).parent().parent().parent().parent().parent().find('[name="number"]').hide();
    } else {
      // set only this row to busy
      cj(button).parent().parent().parent().find('[name="busy"]').show();
      cj(button).parent().parent().parent().find('[name="number"]').hide();
    }

    // finally, send query
    var call = CRM.api3('Bic', 'update', {"country": country_code});
    call.done(
      function(result) {
        for (var key in result.values) {
          if (result.values[key].error != undefined) {
            alert(result.values[key].error);
          }
        }

        location.reload();
      });
    call.fail(
      function(result) {
        alert("The update timed out, but maybe it was partially succesful. You might want to try again.");
        location.reload();
      });
  }
</script>
{/literal}
