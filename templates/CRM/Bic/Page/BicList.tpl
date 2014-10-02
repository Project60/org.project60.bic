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

<table class="display" role="grid">
  <thead>
    <tr class="columnheader">
      <th>{ts}BIC{/ts}</b></th>
      <th>{ts}National Bank ID{/ts}</b></th>
      <th>{ts}Country{/ts}</th>
    </tr>
  </thead>

  <tbody>
    <tr>
      <td><input type="text" value="" name="bic" id="bic"></td>
      <td><input type="text" value="" name="nbid" id="nbid"></td>
      <td>
        <select value="" name="country" id="country">
{foreach from=$countries item=country}
          <option value="{$country}">{$country_names.$country}</option>
{/foreach}
        </select>      
      </td>
    </tr>
  </tbody>
</table>

<table>
  <thead>
    <tr class="columnheader">
      <th>{ts}Name{/ts}</th>
      <th>{ts}BIC{/ts}</th>
      <th>{ts}Description{/ts}</th>
      <th>{ts}Country{/ts}</th>
      <th>{ts}National Bank ID{/ts}</th>
    </tr>
  </thead>
  <tbody id="results">
  </tbody>
</table>

{literal}
<script type="text/javascript">
// general cleanup
cj("#printer-friendly").hide();
cj("#access").hide();

// add accordion
cj(function() {
   cj().crmAccordions();
});

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
        var rowstyle = 'odd-row';
        for (var key in result.values) {
          var line = "<tr class='" + rowstyle + "'>" +
                       "<td>" + result.values[key].title + "</td>" +
                       "<td>" + result.values[key].bic + "</td>" +
                       "<td>" + result.values[key].description + "</td>" +
                       "<td>" + result.values[key].country + "</td>" +
                       "<td>" + result.values[key].nbid + "</td>" +
                     "</tr>";
          cj("#results").append(line);
          if (rowstyle == 'odd-row') { rowstyle = 'even-row'; } else { rowstyle = 'odd-row'; }
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
