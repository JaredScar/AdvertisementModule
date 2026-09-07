{include file='header.tpl'}

<body id="page-top">

<div id="wrapper">

    {include file='sidebar.tpl'}

    <div id="content-wrapper" class="d-flex flex-column">

        <div id="content">

            {include file='navbar.tpl'}

            <div class="container-fluid">

                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">{$FORM_TITLE}</h1>
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{$PANEL_INDEX}">{$DASHBOARD}</a></li>
                        <li class="breadcrumb-item"><a href="{$BACK_LINK}">{$ADVERTISEMENTS}</a></li>
                        <li class="breadcrumb-item active">{$FORM_TITLE}</li>
                    </ol>
                </div>

                {include file='includes/update.tpl'}

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-9">
                                <h5 class="mb-0">{$FORM_TITLE}</h5>
                            </div>
                            <div class="col-md-3 text-md-right">
                                <a href="{$BACK_LINK}" class="btn btn-primary">{$BACK}</a>
                            </div>
                        </div>
                        <hr/>

                        {include file='includes/alerts.tpl'}

                        <form action="" method="post">
                            <div class="form-group">
                                <label for="name">{$NAME}</label>
                                <input type="text" name="name" id="name" class="form-control" value="{$NAME_VALUE}" maxlength="128" required>
                            </div>
                            <div class="form-group">
                                <label for="content">{$CONTENT}</label>
                                <textarea name="content" id="content" class="form-control" rows="10" required>{$CONTENT_VALUE}</textarea>
                                <small class="form-text text-muted">{$CONTENT_INFO}</small>
                            </div>
                            <div class="form-group">
                                <label for="location">{$LOCATION}</label>
                                <select name="location" id="location" class="form-control">
                                    {foreach from=$LOCATION_OPTIONS item=option}
                                        <option value="{$option.value}"{if $option.selected} selected{/if}>{$option.label}</option>
                                    {/foreach}
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="order">{$ORDER}</label>
                                <input type="number" name="order" id="order" class="form-control" value="{$ORDER_VALUE}" step="1">
                            </div>
                            <div class="form-group custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="enabled" name="enabled" value="1"{if $ENABLED_VALUE} checked{/if}>
                                <label class="custom-control-label" for="enabled">{$ENABLED}</label>
                            </div>
                            <input type="hidden" name="token" value="{$TOKEN}">
                            <input type="submit" class="btn btn-primary" value="{$SUBMIT}">
                        </form>
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
