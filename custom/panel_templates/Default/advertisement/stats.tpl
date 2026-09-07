{include file='header.tpl'}

<body id="page-top">

<div id="wrapper">

    {include file='sidebar.tpl'}

    <div id="content-wrapper" class="d-flex flex-column">

        <div id="content">

            {include file='navbar.tpl'}

            <div class="container-fluid">

                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">{$STATISTICS}</h1>
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{$PANEL_INDEX}">{$DASHBOARD}</a></li>
                        <li class="breadcrumb-item"><a href="{$BACK_LINK}">{$ADVERTISEMENTS}</a></li>
                        <li class="breadcrumb-item active">{$STATISTICS}</li>
                    </ol>
                </div>

                {include file='includes/update.tpl'}
                {include file='includes/alerts.tpl'}

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <h5 class="mb-2">{$SELECT_ADVERTISEMENT}</h5>
                                <a href="{$ALL_LINK}" class="btn btn-sm {if !$VIEWING_SINGLE}btn-primary{else}btn-outline-primary{/if}">{$ALL_ADVERTISEMENTS}</a>
                                {foreach from=$AD_OPTIONS item=option}
                                    <a href="{$option.link}" class="btn btn-sm {if $option.selected}btn-primary{else}btn-outline-primary{/if}">{$option.name}</a>
                                {/foreach}
                            </div>
                            <div class="col-md-4 text-md-right">
                                <a href="{$BACK_LINK}" class="btn btn-primary">{$BACK}</a>
                            </div>
                        </div>
                        <hr/>

                        <h5>{$TOTALS}{if $SELECTED_AD} &mdash; {$SELECTED_AD}{/if}</h5>
                        <p>
                            <strong>{$IMPRESSIONS}:</strong> {$TOTAL_IMPRESSIONS}<br/>
                            <strong>{$CLICKS}:</strong> {$TOTAL_CLICKS}
                        </p>

                        {if isset($STATS) && count($STATS)}
                            <h5 class="mt-4">{if $VIEWING_SINGLE}{$DAILY_BREAKDOWN}{else}{$ALL_ADVERTISEMENTS}{/if}</h5>
                            <div class="table-responsive">
                                <table class="table table-borderless table-striped">
                                    <thead>
                                    <tr>
                                        {if $VIEWING_SINGLE}
                                            <th>{$DATE}</th>
                                        {else}
                                            <th>{$NAME}</th>
                                        {/if}
                                        <th>{$IMPRESSIONS}</th>
                                        <th>{$CLICKS}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach from=$STATS item=row}
                                        <tr>
                                            {if $VIEWING_SINGLE}
                                                <td>{$row.date}</td>
                                            {else}
                                                <td><a href="{$row.link}">{$row.name}</a></td>
                                            {/if}
                                            <td>{$row.impressions}</td>
                                            <td>{$row.clicks}</td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        {else}
                            {$NO_STATS}
                        {/if}
                    </div>
                </div>

                <div style="height:1rem;"></div>

            </div>

        </div>

        {include file='footer.tpl'}

    </div>

</div>

{include file='scripts.tpl'}

</body>
</html>
