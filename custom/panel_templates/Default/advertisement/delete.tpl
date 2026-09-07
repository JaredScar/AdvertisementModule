{include file='header.tpl'}

<body id="page-top">

<div id="wrapper">

    {include file='sidebar.tpl'}

    <div id="content-wrapper" class="d-flex flex-column">

        <div id="content">

            {include file='navbar.tpl'}

            <div class="container-fluid">

                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">{$DELETE_ADVERTISEMENT}</h1>
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{$PANEL_INDEX}">{$DASHBOARD}</a></li>
                        <li class="breadcrumb-item"><a href="{$BACK_LINK}">{$ADVERTISEMENTS}</a></li>
                        <li class="breadcrumb-item active">{$DELETE_ADVERTISEMENT}</li>
                    </ol>
                </div>

                {include file='includes/update.tpl'}

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-9">
                                <h5 class="mb-0">{$DELETE_ADVERTISEMENT}</h5>
                            </div>
                            <div class="col-md-3 text-md-right">
                                <a href="{$BACK_LINK}" class="btn btn-primary">{$BACK}</a>
                            </div>
                        </div>
                        <hr/>

                        <p><strong>{$NAME}:</strong> {$NAME_VALUE}</p>
                        <p>{$CONFIRM_DELETE}</p>

                        <form action="" method="post">
                            <input type="hidden" name="token" value="{$TOKEN}">
                            <input type="submit" class="btn btn-danger" value="{$SUBMIT}">
                            <a href="{$BACK_LINK}" class="btn btn-secondary">{$BACK}</a>
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
