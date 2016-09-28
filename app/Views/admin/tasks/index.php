<?php
echo "
<div class='bg-content bg-default'>
    <div class='wrap container'>
            <section>
            <h1>Scheduled Tasks</h1>
            <p class='page_description'>Here you can install, activate or deactivate scheduled tasks and manage their settings.
            Please note that in order to make these tasks running, you must have set a scheduled task pointing to 'cronjobs/run.php'
            either via a Cron Table (Unix server) or via the Scheduled Tasks Manager (Windows server)</p>
            </section>

            <section>
                <h1>Tasks list</h1>
                $cronList
            </section>
    </div>
</div>";
