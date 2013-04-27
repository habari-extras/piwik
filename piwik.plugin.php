<?php

//namespace Habari;

class Piwik extends Plugin
{

	private $dashboard_modules = array();

	/**
	 * Initialize the dashboard modules and add them to the template list.
	 */
	public function action_init()
	{
		$this->dashboard_modules = array(
				'piwik_visitors' => _t('Piwik Visitors', 'piwik'),
				'piwik_browsers' => _t('Piwik Browsers', 'piwik'),
				'piwik_countries' => _t('Piwik Countries', 'piwik'),
				'piwik_page_titles' => _t('Piwik Page Titles', 'piwik'),
				'piwik_os' => _t('Piwik OS', 'piwik'),
			);
		$this->load_text_domain('piwik');
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
		$form->append( 'checkbox', 'trackloggedin', 'option:piwik__trackloggedin', _t('Track logged-in users', 'piwik') );
		$form->append( 'checkbox', 'use_clickheat', 'option:piwik__use_clickheat', _t('Include PiWik Click Heat Plugin JS', 'piwik') );
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
	 * @todo make all these functions into via Plugins::register()
	 * @param FormUI $form The form to allow editing of this block
	 * @param Block $block The block object to edit
	 */
	public function action_block_content_piwik_visitors( Block $block, Theme $theme )
	{
		$date = $block->date ? $block->date : 'previous30';
		$period = $block->period ? $block->period : 'day';
		$block->url = $this->get_block_url('ImageGraph.get', 'VisitsSummary', 'get', 'evolution', $period, $date);
		$block->has_options = true;
	}

	/**
	 * Produce a form for the editing of the time period for reporting
	 * @param FormUI $form The form to allow editing of this block
	 * @param Block $block The block object to edit
	 */
	public function action_block_form_piwik_visitors( FormUI $form, Block $block )
	{
		$this->add_form_options( $form, $block );
	}

	/**
	 * Set the URL for Piwik graph to display Browsers
	 * @param FormUI $form The form to allow editing of this block
	 * @param Block $block The block object to edit
	 */
	public function action_block_content_piwik_browsers( Block $block, Theme $theme )
	{
		$date = $block->date ? $block->date : 'today';
		$period = $block->period ? $block->period : 'month';
		$block->url = $this->get_block_url('ImageGraph.get', 'UserSettings', 'getBrowser', 'horizontalBar', $period, $date);
		$block->has_options = true;
	}

	/**
	 * Produce a form for the editing of the time period for reporting
	 * @param FormUI $form The form to allow editing of this block
	 * @param Block $block The block object to edit
	 */
	public function action_block_form_piwik_browsers( FormUI $form, Block $block )
	{
		$this->add_form_options( $form, $block );
	}

	/**
	 * Set the URL for Piwik graph to display Countries
	 * @param FormUI $form The form to allow editing of this block
	 * @param Block $block The block object to edit
	 */
	public function action_block_content_piwik_countries( Block $block, Theme $theme )
	{
		$date = $block->date ? $block->date : 'today';
		$period = $block->period ? $block->period : 'month';
		$block->url = $this->get_block_url('ImageGraph.get', 'UserCountry', 'getCountry', 'horizontalBar', $period, $date);
		$block->has_options = true;
	}

	/**
	 * Produce a form for the editing of the time period for reporting
	 * @param FormUI $form The form to allow editing of this block
	 * @param Block $block The block object to edit
	 */
	public function action_block_form_piwik_countries( FormUI $form, Block $block )
	{
		$this->add_form_options( $form, $block );
	}

	/**
	 * Set the URL for Piwik graph to display Operating Systems
	 * @param FormUI $form The form to allow editing of this block
	 * @param Block $block The block object to edit
	 */
	public function action_block_content_piwik_os( Block $block, Theme $theme )
	{
		$date = $block->date ? $block->date : 'today';
		$period = $block->period ? $block->period : 'month';
		$block->url = $this->get_block_url('ImageGraph.get', 'UserSettings', 'getOS', 'horizontalBar', $period, $date);
		$block->has_options = true;
	}

	/**
	 * Produce a form for the editing of the time period for reporting
	 * @param FormUI $form The form to allow editing of this block
	 * @param Block $block The block object to edit
	 */
	public function action_block_form_piwik_os( FormUI $form, Block $block )
	{
		$this->add_form_options( $form, $block );
	}

	/**
	 * Set the URL for Piwik graph to display Page Titles
	 * @param FormUI $form The form to allow editing of this block
	 * @param Block $block The block object to edit
	 */
	public function action_block_content_piwik_page_titles( Block $block, Theme $theme )
	{
		$date = $block->date ? $block->date : 'today';
		$period = $block->period ? $block->period : 'month';
		$block->url = $this->get_block_url('ImageGraph.get', 'Actions', 'getPageTitles', 'horizontalBar', $period, $date);
		$block->has_options = true;
	}

	/**
	 * Produce a form for the editing of the time period for reporting
	 * @param FormUI $form The form to allow editing of this block
	 * @param Block $block The block object to edit
	 */
	public function action_block_form_piwik_page_titles( FormUI $form, Block $block )
	{
		$this->add_form_options( $form, $block );
	}

	/**
	 * Add custom options for period and date to the form.
	 *
	 * @param FormUI $form The formui form.
	 * @param Block $blovk The dashboard block.
	 */
	private function add_form_options( FormUI $form, Block $block )
	{
		$period_options = array(
			'day' => _t('day', 'piwik'),
			'week' => _t('week', 'piwik'),
			'month' => _t('month', 'piwik'),
			'year' => _t('year', 'piwik')
			);
		$date_options = array(
			'today' => _t('today', 'piwik'),
			'yesterday' => _t('yesterday', 'piwik'),
			'previous30' => _t('previous 30 days', 'piwik'),
			);
		$form->append( 'select', 'period', $block, _t('Period', 'piwik'), $period_options );
		$form->append( 'select', 'date', $block, _t('Date', 'piwik'), $date_options );
		$form->append( 'submit', 'submit', _t('Save', 'piwik') );
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

		$api_url = sprintf($url, $siteurl, $sitenum, $auth_token, $method, $api_module, $api_action, $graph_type, $period, $date, $query);
		return URL::get( 'auth_ajax', array( 'context' => 'piwik_graph', 'id' => urlencode($api_url) ) );
	}

	/**
	 * Outputs cached blocvk image.
	 *
	 * @param AjaxHandler $handler The AjaxHandler hadling the request.
	 */
	public function action_auth_ajax_piwik_graph( AjaxHandler $handler )
	{
		$api_url = urldecode( $handler->handler_vars->raw('id') );
		if ( !Cache::has('piwik_graphs_' . $api_url) ) {
			// Cache until midnight.
			Cache::set( 'piwik_graphs_' . $api_url, base64_encode(RemoteRequest::get_contents($api_url)) );
		}
		// Serve the cached image.
		header( 'content-type: image/png' );
		echo base64_decode( Cache::get('piwik_graphs_' . $api_url) );
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

		// set title and track 404's'
		$title = 'piwikTracker.setDocumentTitle(document.title);';
		if ( $theme->request->display_404 == true ) {
			$title = <<<KITTENS
piwikTracker.setDocumentTitle('404/URL = '+String(document.location.pathname+document.location.search).replace(/\//g,"%2f") + '/From = ' + String(document.referrer).replace(/\//g,"%2f"));
KITTENS;
		}

		// track tags for individual posts
		$tags = '';
		if ( count($theme->posts) == 1 && $theme->posts instanceof Post ) {
			foreach($theme->posts->tags as $i => $tag){
				$n = $i + 1;
				$tags .= "piwikTracker.setCustomVariable ({$n}, 'Tag', '{$tag->term_display}', 'page');";
			}
		}

		// output the javascript
		echo $this->get_piwik_script($ssl_siteurl, $siteurl, $sitenum, $title, $tags);

		if ( Options::get('piwik__use_clickheat', false) ) {
			// Click Heat integration
			// @todo use groups of entry, page, home, archive, etc. instead of title
			// @todo implement select option to let user choose group, page title, or URL for click tracking
			// @todo implement click quota option
			// $group = $this->get_click_heat_group(); this will check rewrite rule a determine group
			echo $this->get_clickheat_script($sitenum, "(document.title == '' ? '-none-' : encodeURIComponent(document.title))");
		}
	}

	/**
	 * Outputs the piwik tracking javascript for the given parameters
	 * @param string $ssl_siteurl The site's SSL URL.
	 * @param string $siteurl The Site's URL.
	 * @param string $sitenum The site's ID Number.
	 * @param string $title The page title.
	 * @param string $tags A list of tags to track.
	 */
	private function get_piwik_script( $ssl_siteurl, $siteurl, $sitenum, $title, $tags )
	{
		return <<<PUPPIES
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
PUPPIES;
	}

	/**
	 * Outputs the clickheat tracking javascript for the given parameters
	 * @param string $sitenum The site's ID Number.
	 * @param string $group The group to track clicks for. (it uses page title right now)
	 * @param int $click_quota Maximum clicks per page and visitor, next clicks won't be saved (0 = no limit)
	 */
	private function get_clickheat_script( $sitenum, $group, $click_quota = 3 )
	{
		return <<<PONIES
<!-- Piwik ClickHeat -->
<script type="text/javascript">
document.write(unescape("%3Cscript src='" + pkBaseURL + "plugins/ClickHeat/libs/js/clickheat.js' type='text/javascript'%3E%3C/script%3E"));
</script><script type="text/javascript">
try {
clickHeatSite = {$sitenum};
clickHeatGroup = {$group};
clickHeatQuota = {$click_quota};
clickHeatServer = pkBaseURL + 'plugins/ClickHeat/libs/click.php';
initClickHeat();
}
catch( err ) {
}
</script>
<!-- End Piwik ClickHeat -->
PONIES;
	}
}
?>
