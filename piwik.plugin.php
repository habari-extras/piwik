<?php
class Piwik extends Plugin
{
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $this->plugin_id() == $plugin_id ) {
			$actions[]= _t('Configure');
		}
		return $actions;
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $this->plugin_id() == $plugin_id && $action == _t('Configure') ){
			$form = new FormUI('piwik');
			$form->append( 'text', 'siteurl', 'option:piwik__siteurl', _t('Piwik site URL', 'piwik') );
			$form->append( 'text', 'sitenum', 'option:piwik__sitenum', _t('Piwik site number', 'piwik') );
			$form->append( 'checkbox', 'trackloggedin', 'option:piwik__trackloggedin', _t( 'Track logged-in users', 'piwik' ) );
			$form->append( 'submit', 'save', 'Save' );
			$form->on_success( array($this, 'save_config') );
			$form->out();
		}
	}

	/**
	 * Invoked when the before the plugin configurations are saved
	 *
	 * @param FormUI $form The configuration form being saved
	 * @return true
	 */
	public function save_config( FormUI $form )
	{
		Session::notice( _t('Piwik plugin configuration saved', 'piwik') );
		$form->save();
	}

	public function action_plugin_deactivation()
	{
		Options::delete('piwik__siteurl');
		Options::delete('piwik__sitenum');
		Options::delete('piwik__trackloggedin');
	}

	public function action_plugin_activation()
	{
		Options::set('piwik__trackloggedin', false);
	}

	public function theme_footer( Theme $theme )
	{
		// trailing slash url
		$siteurl = Options::get('piwik__siteurl');
		if ( $siteurl{strlen($siteurl)-1} != '/' ) {
 			$siteurl .= '/'; 
		}
		$ssl_siteurl = str_replace("http://", "https://", $siteurl);
		$sitenum = Options::get('piwik__sitenum');

		if ( URL::get_matched_rule()->entire_match == 'user/login') {
			// Login page; don't dipslay
			return;
		}
		// don't track loggedin user
		if ( User::identify()->loggedin && !Options::get('piwik__trackloggedin') ) {
			return;
		}
		echo <<<EOD
<!-- Piwik -->
<script type="text/javascript">
var pkBaseURL = (("https:" == document.location.protocol) ? "${ssl_siteurl}" : "{$siteurl}");
document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
</script><script type="text/javascript">
try {
var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", {$sitenum});
piwikTracker.setDocumentTitle(document.title);
piwikTracker.trackPageView();
piwikTracker.enableLinkTracking();
}
catch( err ) {
}
</script>
<!-- End Piwik Tag -->
EOD;
	}

}
?>
