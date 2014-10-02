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
<table class="display" role="grid">
  <thead>
    <tr>
      <td><b>{ts}Country{/ts}</b></td>
      <td><b>{ts}Count{/ts}</b></td>
      <td><b>{ts}Actions{/ts}</b></td>
    </tr>
  </thead>


  <tbody>
{foreach from=$countries item=country}
    <tr>
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
    <tr>
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


{* Add lookup test *}
<div class="crm-block crm-form-block crm-basic-criteria-form-block">
  <div class="crm-accordion-wrapper crm-case_search-accordion collapsed">
    <div class="crm-accordion-header crm-master-accordion-header">{ts}Lookup Test{/ts}</div>
    
    <div style="display: none;" class="crm-accordion-body">
      <div class="crm-section">
        <div class="label"><label for="bic">{ts}Enter BIC{/ts}</label></div>
        <div class="content"><input type="text" value="" name="bic" id="bic"></div>
        <div class="clear"></div>
      </div>

      <div class="crm-section">
        <div class="label"><label for="nbid">{ts}or: national bank ID{/ts}</label></div>
        <div class="content"><input type="text" value="" name="nbid" id="nbid"></div>
        <div class="clear"></div>
        <div class="label"><label for="country">{ts}and country{/ts}</label></div>
        <div class="content">
            <select value="" name="country" id="country">
{foreach from=$countries item=country}
              <option value="{$country}">{$country_names.$country}</option>
{/foreach}
            </select>
            </div>
        <div class="clear"></div>
      </div>

      <table>
        <thead>
          <tr>
            <td><b>{ts}Country{/ts}</b></td>
            <td><b>{ts}National Bank ID{/ts}</b></td>
            <td><b>{ts}BIC{/ts}</b></td>
          </tr>
        </thead>
        <tbody id="results">
        </tbody>
      </table>
    </div>
  </div>
</div>

{literal}
<script type="text/javascript">
// general cleanup
cj("#printer-friendly").hide();
cj("#access").hide();

// add functions
cj("#bic").val('');
cj("#bic").change(sendQuery);
cj("#nbid").change(sendQuery);
cj("#bic").keypress(enteringBIC);
cj("#nbid").keypress(enteringNBID);

// LOOKUP BUTTONS
function sendQuery() {
  // finally, send query
  var query = {};
  if (cj("#bic").val().length > 0) {
    query['bic'] = cj("#bic").val();
  } else {
    query['nbid'] = cj("#nbid").val();
    query['country'] = cj("#country").val();
  }
  
  // clear table
  cj("#results").empty();

  CRM.api3('Bic', 'get', query).done(
    function(result) {
      if (result.count > 0 ) {
        for (var key in result.values) {
          console.log(result.values[key]);
          var line = "<tr><td>" + result.values[key].country + "</td><td>" + result.values[key].nbid + "</td><td>" + result.values[key].bic + "</td></tr>";
          cj("#results").append(line);
        }
      } else {
        alert("No entries found!");
      }
    });
}

function enteringBIC() {
  cj("#nbid").val('');
}

function enteringNBID() {
  cj("#bic").val('');
}


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
  CRM.api3('Bic', 'update', {"country": country_code}).done(
    function(result) {
      // TODO: update _in table
      location.reload();
    });
}
</script>
{/literal}