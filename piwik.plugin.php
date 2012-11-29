<?php
class Piwik extends Plugin
{

	private $dashboard_modules = array();

	/**
	 * Initialize the dashboard modules and add them to the template list.
	 */
	public function action_init()
	{
		$this->dashboard_modules = array(
				'piwik_visitors' => _t( 'Piwik Visitors (30 Days)', 'piwik'),
				'piwik_browsers' => _t( 'Piwik Browsers (Today)', 'piwik'),
				'piwik_countries' => _t( 'Piwik Countries (Today)', 'piwik'),
				'piwik_page_titles' => _t( 'Piwik Page Titles (Today)', 'piwik'),
				'piwik_os' => _t( 'Piwik OS (Today)', 'piwik'),
			);
	}

	/**
	 * Remove options on deactivation.
	 */
	public function action_plugin_deactivation()
	{
		Options::delete('piwik__siteurl');
		Options::delete('piwik__sitenum');
		Options::delete('piwik__trackloggedin');
		Options::delete('piwik__auth_token');
	}

	/**
	 * Setup defaults on activation.
	 */
	public function action_plugin_activation()
	{
		Options::set('piwik__trackloggedin', false);
	}

	/**
	 * Implement the simple plugin configuration.
	 * @return FormUI The configuration form
	 */
	public function configure()
	{
			$form = new FormUI('piwik');
			$form->append( 'text', 'siteurl', 'option:piwik__siteurl', _t('Piwik site URL', 'piwik') );
			$form->append( 'text', 'sitenum', 'option:piwik__sitenum', _t('Piwik site number', 'piwik') );
			$form->append( 'text', 'auth_token', 'option:piwik__auth_token', _t('Piwik Auth Token', 'piwik') );
			$form->append( 'checkbox', 'trackloggedin', 'option:piwik__trackloggedin', _t( 'Track logged-in users', 'piwik' ) );
			$form->append( 'submit', 'save', _t('Save', 'piwik') );
			$form->on_success( array($this, 'save_config') );
			return $form->get();
	}

	/**
	 * Handle the form submition and save options
	 * @param FormUI $form The FormUI that was submitted
	 */
	public function save_config( FormUI $form )
	{
		Session::notice( _t('Piwik plugin configuration saved', 'piwik') );
		$form->save();
	}

	/**
	 * Return a list of blocks that can be used for the dashboard
	 * @param array $block_list An array of block names, indexed by unique string identifiers
	 * @return array The altered array
	 */
	public function filter_dashboard_block_list( Array $block_list )
	{
		foreach( $this->dashboard_modules as $id => $name ) {
			$block_list[$id] = $name;
			$this->add_template( 'dashboard.block.' . $id, __DIR__ . '/dashboard.block.piwik.php' );
		}
		return $block_list;
	}

	/**
	 * Set the URL for Piwik graph to display Visitors
	 * @todo make time periods configurable per block
	 */
	public function action_block_content_piwik_visitors( Block $block, Theme $theme )
	{
		$block->url = $this->get_block_url('ImageGraph.get', 'VisitsSummary', 'get', 'evolution', 'day', 'previous30');
	}

	/**
	 * Set the URL for Piwik graph to display Browsers
	 */
	public function action_block_content_piwik_browsers( Block $block, Theme $theme )
	{
		$block->url = $this->get_block_url('ImageGraph.get', 'UserSettings', 'getBrowser', 'horizontalBar', 'month', 'today');
	}

	/**
	 * Set the URL for Piwik graph to display Countries
	 */
	public function action_block_content_piwik_countries( Block $block, Theme $theme )
	{
		$block->url = $this->get_block_url('ImageGraph.get', 'UserCountry', 'getCountry', 'horizontalBar', 'month', 'today');
	}

	/**
	 * Set the URL for Piwik graph to display Operating Systems
	 */
	public function action_block_content_piwik_os( Block $block, Theme $theme )
	{
		$block->url = $this->get_block_url('ImageGraph.get', 'UserSettings', 'getOS', 'horizontalBar', 'month', 'today');
	}

	/**
	 * Set the URL for Piwik graph to display Page Titles
	 */
	public function action_block_content_piwik_page_titles( Block $block, Theme $theme )
	{
		$block->url = $this->get_block_url('ImageGraph.get', 'Actions', 'getPageTitles', 'horizontalBar', 'month', 'today');
	}

	/**
	 * Build the URL to the Graph image based on requested api methods.
	 *
	 * @param string $method The API method to process.
	 * @param string $api_module The API module to call.
	 * @param string $api_action The API action to call.
	 * @param string $graph_type The graph type to produce (evolution, verticalBar, horizontalBar, pie, 3dPie).
	 * @param string $period The time period to collect stats for.
	 * @param string $date The time to display stats for.
	 * @param string $query An additional query string to append to the request, must start with & (optional).
	 */
	private function get_block_url( $method, $api_module, $api_action, $graph_type, $period, $date, $query = null )
	{
		$siteurl = Options::get('piwik__siteurl');
		if ( $siteurl{strlen($siteurl)-1} != '/' ) {
			$siteurl .= '/';
		}
		$ssl_siteurl = str_replace("http://", "https://", $siteurl);
		$sitenum = Options::get('piwik__sitenum');
		$auth_token = Options::get('piwik__auth_token');

		$url = '%sindex.php?module=API&idSite=%s&token_auth=%s&width=420&height=200&method=%s&apiModule=%s&apiAction=%s&graphType=%s&period=%s&date=%s%s';

		return sprintf($url, $siteurl, $sitenum, $auth_token, $method, $api_module, $api_action, $graph_type, $period, $date, $query);
	}

	/**
	 * Outputs the Javascript tracking code in the theme's footer.
	 *
	 * @param Theme $theme The current Theme to add the tracking JS to.
	 */
	public function theme_footer( Theme $theme )
	{
		// trailing slash url
		$siteurl = Options::get('piwik__siteurl');
		if ( $siteurl{strlen($siteurl)-1} != '/' ) {
			$siteurl .= '/';
		}
		$ssl_siteurl = str_replace("http://", "https://", $siteurl);
		$sitenum = Options::get('piwik__sitenum');

		if ( URL::get_matched_rule()->entire_match == 'auth/login') {
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
