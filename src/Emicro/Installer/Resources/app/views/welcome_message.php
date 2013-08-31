<style type="text/css">

    ::selection{ background-color: #E13300; color: white; }
    ::moz-selection{ background-color: #E13300; color: white; }
    ::webkit-selection{ background-color: #E13300; color: white; }

    body {
        background-color: #fff;
        margin: 40px;
        font: 13px/20px normal Helvetica, Arial, sans-serif;
        color: #4F5155;
    }

    a {
        color: #003399;
        background-color: transparent;
        font-weight: normal;
    }

    h1 {
        color: #444;
        background-color: transparent;
        border-bottom: 1px solid #D0D0D0;
        font-size: 19px;
        font-weight: normal;
        margin: 0 0 14px 0;
        padding: 14px 15px 10px 15px;
    }

    code {
        font-family: Consolas, Monaco, Courier New, Courier, monospace;
        font-size: 12px;
        background-color: #f9f9f9;
        border: 1px solid #D0D0D0;
        color: #002166;
        display: block;
        margin: 14px 0 14px 0;
        padding: 12px 10px 12px 10px;
    }

    #body{
        margin: 0 15px 0 15px;
    }

    p.footer{
        text-align: right;
        font-size: 11px;
        border-top: 1px solid #D0D0D0;
        line-height: 32px;
        padding: 0 10px 0 10px;
        margin: 20px 0 0 0;
    }

    #container{
        margin: 10px;
        border: 1px solid #D0D0D0;
        -webkit-box-shadow: 0 0 8px #D0D0D0;
    }
</style>

<h1><?php __t('Welcome to CodeIgniter!', 'site'); ?>
    <?php echo anchor($this->ezuri->logout(),'log out','style="float:right;font-size:12px"'); ?>
</h1>

<div id="body">
    <p><?php __t('The page you are looking at is being generated dynamically by CodeIgniter.', 'site'); ?></p>

    <p><?php __t('If you would like to edit this page you\'ll find it located at:', 'site') ?></p>
    <code><?php echo __FILE__ ?></code>

    <p><?php __t('The corresponding controller for this page is found at:', 'site'); ?></p>
    <code><?PHP echo realpath(APPPATH . "/controllers/welcome.php") ?></code>

    <p><?php __t('If you are exploring CodeIgniter for the very first time, you should start by reading the', 'site'); ?>
        <a href="http://ellislab.com/codeigniter/user-guide/‎">User Guide</a>.</p>
</div>

<p class="footer"><?php __t('Page rendered in', 'site'); ?> <strong>{elapsed_time}</strong>
    <?php __t('seconds', 'site'); ?></p>
