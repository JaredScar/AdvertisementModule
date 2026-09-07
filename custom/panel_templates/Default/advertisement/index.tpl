{include file='header.tpl'}

<body id="page-top">

<div id="wrapper">

    {include file='sidebar.tpl'}

    <div id="content-wrapper" class="d-flex flex-column">

        <div id="content">

            {include file='navbar.tpl'}

            <div class="container-fluid">

                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">{$MANAGE_ADVERTISEMENTS}</h1>
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{$PANEL_INDEX}">{$DASHBOARD}</a></li>
                        <li class="breadcrumb-item active">{$ADVERTISEMENTS}</li>
                    </ol>
                </div>

                {include file='includes/update.tpl'}

                {include file='includes/alerts.tpl'}

                {if $CAN_SETTINGS}
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">{$GLOBAL_SETTINGS}</h6>
                    </div>
                    <div class="card-body">
                        <p>{$PLACEMENT_INFO}</p>
                        <form action="" method="post">
                            <div class="form-group custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="widget_enabled" name="widget_enabled" value="1"{if $WIDGET_ENABLED} checked{/if}>
                                <label class="custom-control-label" for="widget_enabled">{$ENABLE_WIDGET}</label>
                            </div>
                            <div class="form-group custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="header_enabled" name="header_enabled" value="1"{if $HEADER_ENABLED} checked{/if}>
                                <label class="custom-control-label" for="header_enabled">{$ENABLE_HEADER}</label>
                            </div>
                            <div class="form-group custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="footer_enabled" name="footer_enabled" value="1"{if $FOOTER_ENABLED} checked{/if}>
                                <label class="custom-control-label" for="footer_enabled">{$ENABLE_FOOTER}</label>
                            </div>
                            <input type="hidden" name="token" value="{$TOKEN}">
                            <input type="submit" class="btn btn-primary" value="{$SUBMIT}">
                        </form>
                    </div>
                </div>
                {/if}

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-9">
                                <h5 class="mb-0">{$ADVERTISEMENTS}</h5>
                            </div>
                            <div class="col-md-3 text-md-right">
                                {if $CAN_CREATE}
                                    <a href="{$NEW_LINK}" class="btn btn-primary"><i class="fas fa-plus-circle"></i> {$NEW}</a>
                                {/if}
                            </div>
                        </div>

                        {if isset($ADS_LIST) && count($ADS_LIST)}
                            <div class="table-responsive">
                                <table class="table table-borderless table-striped">
                                    <thead>
                                    <tr>
                                        <th>{$ORDER}</th>
                                        <th>{$NAME}</th>
                                        <th>{$LOCATION}</th>
                                        <th>{$ENABLED}</th>
                                        <th>{$CREATOR}</th>
                                        <th>{$ACTIONS}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach from=$ADS_LIST item=ad}
                                        <tr>
                                            <td>{$ad.order}</td>
                                            <td>{$ad.name}</td>
                                            <td>{$ad.location}</td>
                                            <td>{if $ad.enabled}{$YES}{else}{$NO}{/if}</td>
                                            <td>{$ad.creator}</td>
                                            <td>
                                                {if $CAN_CREATE}
                                                    <a href="{$ad.edit_link}" class="btn btn-warning btn-sm"><i class="fa fa-fw fa-edit"></i></a>
                                                {/if}
                                                {if $CAN_STATS}
                                                    <a href="{$ad.stats_link}" class="btn btn-info btn-sm"><i class="fa fa-fw fa-chart-bar"></i></a>
                                                {/if}
                                                {if $CAN_DELETE}
                                                    <a href="{$ad.delete_link}" class="btn btn-danger btn-sm"><i class="fa fa-fw fa-trash"></i></a>
                                                {/if}
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        {else}
                            {$NO_ADVERTISEMENTS}
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
