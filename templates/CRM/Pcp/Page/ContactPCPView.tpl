


{strip}
<table id="options" class="display">
  <thead>
    <tr>
    <th>{ts}Page Title{/ts}</th>
    <th>{ts}Status{/ts}</th>
    <th>{ts}Contribution Page / Event{/ts}</th>
    <th>{ts}Contributions{/ts}</th>
    <th>{ts}Raised{/ts}</th>
    <th>{ts}Goal{/ts}</th>
    <th>{ts}Edit{/ts}</th>
    <th></th>
    </tr>
  </thead>
  <tbody>
  {foreach from=$rows item=row}
  <tr id="row_{$row.id}" class="{$row.class}">
    <td><a href="{crmURL p='civicrm/pcp/info' q="reset=1&id=`$row.id`" fe='true'}" title="{ts}View Personal Campaign Page{/ts}" target="_blank">{$row.title}</a></td>
    <td>{$row.status_id}</td>
    <td><a href="{$row.page_url}" title="{ts}View page{/ts}" target="_blank">{$row.page_title}</td>
    <td>{$row.total_cons}</td>
    <td>${$row.cons_amount}</td>
    <td>${$row.goal_amount}</td>
   	<td><a href="{$row.edit_url}" target="_blank">Edit</a></td>
    
  </tr>
  {/foreach}
  </tbody>
</table>
{/strip}

