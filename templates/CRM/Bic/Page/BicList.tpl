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
  This page allows you to find information about a bank by specifying its BIC or National ID.
  If you don't find any information here, you may want to <a href='bicImport'>update your bank list</a>.
{/ts}</div>

<table class="display" role="grid">
  <thead>
    <tr class="columnheader">
      <th>{ts}Country{/ts}</th>
      <th>{ts}BIC{/ts}</b></th>
      <th>{ts}National Bank ID{/ts}</b></th>
    </tr>
  </thead>

  <tbody>
    <tr>
      <td>
        <select value="" name="country" id="country">
{foreach from=$countries item=country}
          <option value="{$country}">{$country_names.$country}</option>
{/foreach}
        </select>      
      </td>
      <td><input type="text" value="" name="bic" id="bic" style="text-transform:uppercase;"></td>
      <td><input type="text" value="" name="nbid" id="nbid"></td>
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
  // Empty fields when loading the page
  cleanFilters();
  
  // Perform a search when a field changes
  cj("#country").change(sendQuery);
  cj("#bic").change(sendQuery);
  cj("#nbid").change(sendQuery);
  
  // Empty BIC when writting NBID and viceversa
  cj("#bic").keyup(enteringBIC);
  cj("#nbid").keyup(enteringNBID);

  function sendQuery() {
    // Update list of banks
    var query = {};
    query['bic'] = cj("#bic").val();
    query['nbid'] = cj("#nbid").val();
    query['country'] = cj("#country").val();
    
    CRM.api3('Bic', 'get', query).done(
      function(result) {
        if (result.count > 0 ) {
          var rowstyle = 'odd-row';
          var line;
          for (var key in result.values) {
            line += "<tr class='" + rowstyle + "'>" +
                        "<td>" + result.values[key].title + "</td>" +
                        "<td>" + result.values[key].bic + "</td>" +
                        "<td>" + result.values[key].description + "</td>" +
                        "<td>" + result.values[key].country + "</td>" +
                        "<td>" + result.values[key].nbid + "</td>" +
                      "</tr>";
                      
            if (rowstyle == 'odd-row') { rowstyle = 'even-row'; } else { rowstyle = 'odd-row'; }
          }
          
          cj("#results").empty();
          cj("#results").append(line);
          
        } else {
          var line = "<tr class='odd-row'><td colspan='5'>Could not find any match with this criteria. You may want to <a href='bicImport'>update your bank list</a>.</td></tr>";
          cj("#results").append(line);
        }
      });
  }

  function enteringBIC() {
    cj("#nbid").val('');

    if(cj("#bic").val().length >= 3) {
      sendQuery();
    }
  }

  function enteringNBID() {
    cj("#bic").val('');

    if(cj("#nbid").val().length >= 3) {
      sendQuery();
    }
  }
  
  function cleanFilters() {
    cj("#nbid").val('');
    cj("#bic").val('');
  }
</script>
{/literal}
