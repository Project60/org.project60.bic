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

{crmScope extensionKey='org.project60.bic'}
{if $show_message}
  <div id="help">{ts}This page allows you to find banks and additional information. If you can't find the bank you're looking for, you may want to <a href='bicImport'>update your bank list</a>.{/ts}</div>
{/if}


<br/>
<h3>{ts}Find Bank by IBAN{/ts}</h3>
<table class="display" role="grid">
  <thead>
    <tr class="columnheader">
      <th>{ts}IBAN{/ts}</th>
    </tr>
  </thead>

  <tbody>
    <tr>
      <td><input size="40" type="text" value="" name="iban" id="iban" style="text-transform:uppercase;"></td>
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
  <tbody id="iban_results">
  </tbody>
</table>




<br/><br/>
<h3>{ts}Find Bank by National ID{/ts}</h3>
<table class="display" role="grid">
  <thead>
    <tr class="columnheader">
      <th>{ts}Country{/ts}</th>
      <th>{ts}National Bank ID{/ts}</b></th>
      <th>{ts}BIC{/ts}</b></th>
    </tr>
  </thead>

  <tbody>
    <tr>
      <td>
        <select value="" name="country" id="country">
{foreach from=$countries item=country}
          <option value="{$country}" {if $country eq $default_country}selected{/if}>{$country_names.$country}</option>
{/foreach}
        </select>      
      </td>
      <td><input type="text" value="" name="nbid" id="nbid"></td>
      <td><input type="text" value="" name="bic" id="bic" style="text-transform:uppercase;"></td>
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
  cj("#iban").change(sendIBANQuery);
  
  // Empty BIC when writting NBID and viceversa
  cj("#bic").keyup(enteringBIC);
  cj("#nbid").keyup(enteringNBID);
  cj("#iban").keyup(enteringIBAN);

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
          cj("#results").empty();
          cj("#results").append(line);
        }
      });
  }

  function sendIBANQuery() {
    // Update list of banks
    var query = {};
    query['iban'] = cj("#iban").val();

    // strip whitespaces and format upper case
    var reSpaceAndMinus = new RegExp('[\\s-]', 'g');
    query['iban'] = query['iban'].replace(reSpaceAndMinus, "");
    query['iban'] = query['iban'].toUpperCase();

    
    CRM.api3('Bic', 'findbyiban', query).done(
      function(result) {
        if (result.count != 0 ) {
          line = "<tr class='odd-row'>" +
                      "<td>" + result.title + "</td>" +
                      "<td>" + result.bic + "</td>" +
                      "<td>" + result.description + "</td>" +
                      "<td>" + result.country + "</td>" +
                      "<td>" + result.nbid + "</td>" +
                    "</tr>";
          
          cj("#iban_results").empty();
          cj("#iban_results").append(line);
          
        } else {
          var line = "<tr class='odd-row'><td colspan='5'>Could not find any match with this criteria. You may want to <a href='bicImport'>update your bank list</a>.</td></tr>";
          cj("#iban_results").empty();
          cj("#iban_results").append(line);
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
  
  function enteringIBAN() {
    if(cj("#iban").val().length >= 10) {
      sendIBANQuery();
    }
  }

  function cleanFilters() {
    cj("#nbid").val('');
    cj("#bic").val('');
  }
</script>
{/literal}
{/crmScope}