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
			$form->append( 'text', 'auth_token', 'option:piwik__auth_token', _t('Piwik Auth Token', 'piwik') );
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

	public function action_init()
	{
		$this->add_template( 'dashboard.block.piwik_visitors', __DIR__ . '/dashboard.block.piwik.php' );
		$this->add_template( 'dashboard.block.piwik_browsers', __DIR__ . '/dashboard.block.piwik.php' );
		$this->add_template( 'dashboard.block.piwik_countries', __DIR__ . '/dashboard.block.piwik.php' );
	}

	/**
	* Return a list of blocks that can be used for the dashboard
	* @param array $block_list An array of block names, indexed by unique string identifiers
	* @return array The altered array
	*/
	public function filter_dashboard_block_list($block_list)
	{
		$block_list['piwik_visitors'] = _t( 'Piwik Visitors', 'piwik');
		$block_list['piwik_browsers'] = _t( 'Piwik Browsers', 'piwik');
		$block_list['piwik_countries'] = _t( 'Piwik Countries', 'piwik');
		return $block_list;
	}

	public function action_block_content_piwik_visitors($block, $theme)
	{
		$block->url = $this->get_block_url("&method=ImageGraph.get&apiModule=VisitsSummary&apiAction=get&graphType=evolution&period=day&date=previous30");
	}

	public function action_block_content_piwik_browsers($block, $theme)
	{
		$block->url = $this->get_block_url("&method=ImageGraph.get&apiModule=UserSettings&apiAction=getBrowser&graphType=horizontalBar&period=month&date=today");
	}
	
	public function action_block_content_piwik_countries($block, $theme)
	{
		$block->url = $this->get_block_url("&method=ImageGraph.get&apiModule=UserCountry&apiAction=getCountry&graphType=verticalBar&period=month&date=today");
	}

	private function get_block_url($query)
	{
		$siteurl = Options::get('piwik__siteurl');
        if ( $siteurl{strlen($siteurl)-1} != '/' ) {
            $siteurl .= '/';
        }
        $ssl_siteurl = str_replace("http://", "https://", $siteurl);
        $sitenum = Options::get('piwik__sitenum');
		$auth_token = Options::get('piwik__auth_token');
		
		return "{$siteurl}index.php?module=API&idSite={$sitenum}&token_auth={$auth_token}{$query}&width=420&height=200";
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
        $title = 'piwikTracker.setDocumentTitle(document.title);';
        if ( $theme->request->display_404 == true ) {
            $title = <<<LMNO
piwikTracker.setDocumentTitle('404/URL = '+String(document.location.pathname+document.location.search).replace(/\//g,"%2f") + '/From = ' + String(document.referrer).replace(/\//g,"%2f"));
LMNO;
        }
        $tags = '';
        if ( count($theme->posts) == 1 && $theme->posts instanceof Post ) {
            foreach($theme->posts->tags as $i => $tag){
                $n = $i + 1;
                $tags .= "piwikTracker.setCustomVariable ({$n}, 'Tag', '{$tag->term_display}', 'page');";
            }
        }
		echo <<<EOD
<!-- Piwik -->
<script type="text/javascript">
var pkBaseURL = (("https:" == document.location.protocol) ? "${ssl_siteurl}" : "{$siteurl}");
document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
</script><script type="text/javascript">
try {
var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", {$sitenum});
{$title}
{$tags}
piwikTracker.trackPageView();
piwikTracker.enableLinkTracking();
}
catch( err ) {
}
</script><noscript><p><img src="{$siteurl}piwik.php?idsite={$sitenum}" style="border:0" alt="" /></p></noscript>
<!-- End Piwik Tag -->
EOD;
	}

}
?>
