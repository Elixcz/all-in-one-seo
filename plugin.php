<?php
/** All in One SEO
 *
 * @author Elix
 * @url https://elix.mzf.cz
 * @email elix.code@gmail.com
 * @version 1.0.0
 */
class pluginAllInOneSeo extends Plugin {

	public function init() {
		$this->dbFields = array(
			'defaultImage'       => '',
			'fbAppId'            => '',
			'enableCanonical'    => true,
			'enableRobots'       => true,
			'enableOpenGraph'    => true,
			'enableTwitterCards' => true,
			'twitterSite'        => '',
			'twitterCreator'     => '',
			'twitterWidgetColor' => 'light',
			'twitterWidgetCsp'   => 'on',
			'twitterWidgetDnt'   => 'on',
			'googleVerification' => '',
		);
	}

	public function siteHead()
	{
		global $url;
		global $page;
		global $site;
		global $WHERE_AM_I;
		global $content;

		// First line in HTML
		$html = '<!-- Plugin Ultimate SEO -->' . PHP_EOL;

		// Meta tag Robots
		if( $this->getValue('enableRobots') )
		{
			$html .= '<meta name="robots" content="index, follow, archive">'.PHP_EOL;
		}else{
			$html .= '<meta name="robots" content="noindex, nofollow, noarchive">'.PHP_EOL;
		}

		// Meta tag canonical
		if( $this->getValue('enableCanonical') )
		{
			if ( $WHERE_AM_I === 'home' )
			{
				$html .= '<link rel="canonical" href="'.DOMAIN_BASE.'">'.PHP_EOL;
			} elseif ( $WHERE_AM_I === 'page' )
			{
				global $page;
				$html .= '<link rel="canonical" href="'.$page->permalink().'">'.PHP_EOL;
			} elseif ($WHERE_AM_I === 'category')
			{
				$categoryKey = $url->slug();
				$category = new Category( $categoryKey );

				$html .= '<link rel="canonical" href="' . $category->permalink() . '">'.PHP_EOL;
			} elseif ($WHERE_AM_I === 'tag')
			{
				$tagKey = $url->slug();
				$tag = new Tag( $tagKey );

				$html .= '<link rel="canonical" href="'.$tag->permalink().'">'.PHP_EOL;
			}
		}

		// FB App ID
		if ( Text::isNotEmpty( $this->getValue('fbAppId') ) )
		{
			$html .= '<meta property="fb:app_id" content="' . $this->getValue('fbAppId') . '">'.PHP_EOL;
		}

		// Open graph
		if( $this->getValue('enableOpenGraph'))
		{
			// default settings
			$og = array(
				'locale'	  => $site->locale(),
				'type'		  => 'website',
				'title'		  => $site->title(),
				'description' => $site->description(),
				'url'		  => $site->url(),
				'image'		  => '',
				'siteName'	  => $site->title()
			);
			$pageContent = '';

			// if page
			if( $WHERE_AM_I == 'page' )
			{
				$og['type']		   = 'article';
				$og['title']	   = $page->title();
				$og['description'] = $page->description();
				$og['url']		   = $page->permalink( $absolute = true );
				$og['image'] 	   = $page->coverImage( $absolute = true);
				$pageContent       = $page->content();
			// if other
			}else{
				if ( isset( $content[0] ) )
				{
					$og['image'] = $content[0]->coverImage( $absolute = true );
					$pageContent = $content[0]->content();
				}else{
					if ( ! empty( $this->getValue('defaultImage') ) )
					{
						$og['image'] = $this->getValue('defaultImage');
					}
				}
			}

			if ( empty( $og['image'] ) && !empty( $pageContent ) )
			{
				$src = DOM::getFirstImage( $pageContent );
				if ( $src !== false )
				{
					$og['image'] = $src;
				}
			}
			$og['description'] = $this->content_excerpt( $og['description'] , 150, '...');

			$html .= '<meta property="og:locale" content="' . $og['locale'] . '">'.PHP_EOL;
			$html .= '<meta property="og:type" content="' . $og['type'] . '">'.PHP_EOL;
			$html .= '<meta property="og:title" content="' .$og['title'] . '">'.PHP_EOL;
			$html .= '<meta property="og:description" content="' . $og['description'] . '">'.PHP_EOL;
			$html .= '<meta property="og:url" content="' . $og['url'] . '">'.PHP_EOL;
			$html .= '<meta property="og:site_name" content="' . $og['siteName'] . '">'.PHP_EOL;
			$html .= '<meta property="og:image" content="' . $og['image'] . '">'.PHP_EOL;
			$html .= '<meta name="twitter:card" content="summary_large_image">'.PHP_EOL;
			$html .= '<meta name="twitter:site" content="' . $this->getValue('twitterSite') . '">'.PHP_EOL;
			$html .= '<meta name="twitter:creator" content="' . $this->getValue('twitterCreator') . '">'.PHP_EOL;
			$html .= '<meta name="twitter:description" content="' . $og['description'] . '">'.PHP_EOL;
			$html .= '<meta name="twitter:image" content="' . $og['image'] . '">'.PHP_EOL;

			//unset( $pageContent );
		}

		if( $this->getValue('twitterWidgetColor') == 'dark')
		{
			$html .= '<meta name="twitter:widgets:theme" content="dark">' . PHP_EOL;
		}else{
			$html .= '<meta name="twitter:widgets:theme" content="light">' . PHP_EOL;
		}

		if( $this->getValue('twitterWidgetCsp') == 'on')
		{
			$html .= '<meta name="twitter:widgets:csp" content="on">' . PHP_EOL;
		}else{
			$html .= '<meta name="twitter:widgets:csp" content="off">' . PHP_EOL;
		}

		if( $this->getValue('twitterWidgetDnt') == 'on')
		{
			$html .= '<meta name="twitter:dnt" content="on">' . PHP_EOL;
		}else{
			$html .= '<meta name="twitter:dnt" content="off">' . PHP_EOL;
		}

		if( !empty( $this->getValue('googleVerification') ) )
		{
			$html .= '<meta name="google-site-verification" content="' . $this->getValue('googleVerification') .'">' . PHP_EOL;
		}

		$html .= '<!-- /Plugin Ultimate SEO -->' . PHP_EOL . PHP_EOL;
		return $html;
	}

	public function form()
	{
		global $L;
		$html = '';

		// Check if the plugin Twitter cards is activated
		if ( pluginActivated('pluginTwitterCards') )
		{
			$html .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
			$html .= $L->get('Plugin <strong>Twitter Cards</strong> is active. Deactivate it!');
			$html .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
			$html .= '</div>';
		}
		// Check if the plugin Open graph is activated
		if ( pluginActivated('pluginOpenGraph') )
		{
			$html .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
			$html .= $L->get('Plugin <strong>Open Graph</strong> is active. Deactivate it!');
			$html .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
			$html .= '</div>';
		}
		// Check if the plugin Canonical is activated
		if ( pluginActivated('pluginCanonical') )
		{
			$html .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
			$html .= $L->get('Plugin <strong>Canonical</strong> is active. Deactivate it!');
			$html .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
			$html .= '</div>';
		}
		// Check if the plugin Robots is activated
		if ( pluginActivated('pluginRobots') )
		{
			$html .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
			$html .= $L->get('Plugin <strong>Robots</strong> is active. Deactivate it!');
			$html .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
			$html .= '</div>';
		}

		$html .= '<hr>';

		$html .= '<h4 class="border-bottom border-info text-info">' . $L->get('Meta tags') . '</h4>';
		$html .= '<div class="mb-5">';
        $html .= '<label class="form-label" for="enableRobots">' . $L->get('Meta tag robots') . '</label>';
        $html .= '<select class="form-select" id="enableRobots" name="enableRobots" aria-describedby="enableRobots">';
        $html .= '<option value="true" ' . ($this->getValue('enableRobots') === true ? 'selected' : '') . '>' . $L->get('Enabled') . '</option>';
        $html .= '<option value="false" ' . ($this->getValue('enableRobots') === false ? 'selected' : '') . '>' . $L->get('Disabled') . '</option>';
        $html .= '</select>';
        $html .= '<small class="form-text text-muted" id="enableRobots">' . $L->get('This option adds a robots meta tag to the site header, which tells search robots whether or not to index your site.') . '</small>';
        $html .= '</div>';

		$html .= '<div class="mb-5">';
        $html .= '<label class="form-label" for="enableCanonical">' . $L->get('Meta tag canonical') . '</label>';
        $html .= '<select class="form-select" id="enableCanonical" name="enableCanonical" aria-describedby="enableCanonical">';
        $html .= '<option value="true" ' . ($this->getValue('enableCanonical') === true ? 'selected' : '') . '>' . $L->get('Enabled') . '</option>';
        $html .= '<option value="false" ' . ($this->getValue('enableCanonical') === false ? 'selected' : '') . '>' . $L->get('Disabled') . '</option>';
        $html .= '</select>';
        $html .= '<small class="form-text text-muted" id="enableCanonical">' . $L->get('This option adds a canonical meta tag to the site header, which prevents search engines from indexing duplicate content.') . '</small>';
        $html .= '</div>';

        $html .= '<div class="mb-5">';
        $html .= '<label class="form-label" for="enableOpenGraph">' . $L->get('Open Graph') . '</label>';
        $html .= '<select class="form-select" id="enableOpenGraph" name="enableOpenGraph" aria-describedby="enableOpenGraph">';
        $html .= '<option value="true" ' . ($this->getValue('enableOpenGraph') === true ? 'selected' : '') . '>' . $L->get('Enabled') . '</option>';
        $html .= '<option value="false" ' . ($this->getValue('enableOpenGraph') === false ? 'selected' : '') . '>' . $L->get('Disabled') . '</option>';
        $html .= '</select>';
        $html .= '<small class="form-text text-muted" id="enableOpenGraph">' . $L->get('This option adds a Open Graph meta tag to the site header.') . '</small>';
        $html .= '</div>';

        $html .= '<div class="mb-5">';
        $html .= '<label class="form-label" for="defaultImage">'.$L->get('Default image for sharing').'</label>';
        $html .= '<input class="form-control" id="defaultImage" name="defaultImage" type="text" aria-describedby="defaultImage" value="' . $this->getValue('defaultImage') . '" placeholder="' . DOMAIN_BASE . '">';
        $html .= '<small class="form-text text-muted" id="defaultImage">'.$L->get('Set the URL to the image that will be displayed when sharing a page without a cover image.').'</small>';
        $html .= '</div>';

		$html .= '<h4 class="border-bottom border-info text-info">' . $L->get('Facebook') . '</h4>';
        $html .= '<div class="mb-5">';
        $html .= '<label class="form-label" for="fbAppId">'.$L->get('Facebook App ID').'</label>';
        $html .= '<input class="form-control" id="fbAppId" name="fbAppId" type="text" aria-describedby="fbAppId" value="'.$this->getValue('fbAppId').'">';
        $html .= '<small class="form-text text-muted" id="fbAppId">'.$L->get('Set your Facebook app id').'</small>';
        $html .= '</div>';

		$html .= '<h4 class="border-bottom border-info text-info">' . $L->get('Twitter') . '</h4>';
        $html .= '<div class="mb-5">';
        $html .= '<label class="form-label" for="twitterSite">'.$L->get('Site profile on Twitter').'</label>';
        $html .= '<input class="form-control" id="twitterSite" name="twitterSite" type="text" aria-describedby="twitterSite" value="'.$this->getValue('twitterSite').'" placeholder="@YourSite">';
        $html .= '<small class="form-text text-muted" id="twitterSite">'.$L->get('Set your Twitter site profile').'</small>';
        $html .= '</div>';

        $html .= '<div class="mb-5">';
        $html .= '<label class="form-label" for="twitterCreator">'.$L->get('Author profile on Twitter').'</label>';
        $html .= '<input class="form-control" id="twitterCreator" name="twitterCreator" type="text" aria-describedby="twitterCreator" value="'.$this->getValue('twitterCreator').'" placeholder="@PostsAuthor">';
        $html .= '<small class="form-text text-muted" id="twitterCreator">'.$L->get('Set posts author Twitter profile').'</small>';
        $html .= '</div>';

        $html .= '<div class="mb-5">';
        $html .= '<label class="form-label" for="twitterWidgetColor">' . $L->get('Twitter widgets color') . '</label>';
        $html .= '<select class="form-select" id="twitterWidgetColor" name="twitterWidgetColor" aria-describedby="twitterWidgetColor">';
        $html .= '<option value="light" ' . ($this->getValue('twitterWidgetColor') === 'light' ? 'selected' : '') . '>' . $L->get('Light') . '</option>';
        $html .= '<option value="dark" ' . ($this->getValue('twitterWidgetColor') === 'dark' ? 'selected' : '') . '>' . $L->get('Dark') . '</option>';
        $html .= '</select>';
        $html .= '<small class="form-text text-muted" id="twitterWidgetColor">' . $L->get('This option override the default light theme preference for an embedded Tweet or an embedded Timeline.') . '</small>';
        $html .= '</div>';

        $html .= '<div class="mb-5">';
        $html .= '<label class="form-label" for="twitterWidgetCsp">' . $L->get('Twitter CSP option') . '</label>';
        $html .= '<select class="form-select" id="twitterWidgetCsp" name="twitterWidgetCsp" aria-describedby="twitterWidgetCsp">';
        $html .= '<option value="on" ' . ($this->getValue('twitterWidgetCsp') === 'on' ? 'selected' : '') . '>' . $L->get('Enable') . '</option>';
        $html .= '<option value="off" ' . ($this->getValue('twitterWidgetCsp') === 'off' ? 'selected' : '') . '>' . $L->get('Disable') . '</option>';
        $html .= '</select>';
        $html .= '<small class="form-text text-muted" id="twitterWidgetCsp">' . $L->get('An embedded Tweet or embedded Timeline may display with restricted capabilities when a Content Security Policy restricts inline loading of Twitter. Set csp=on to turn off functionality which could display Content Security Policy warnings on your site.') . '</small>';
        $html .= '</div>';

        $html .= '<div class="mb-5">';
        $html .= '<label class="form-label" for="twitterWidgetDnt">' . $L->get('Twitter Do not track option') . '</label>';
        $html .= '<select class="form-select" id="twitterWidgetDnt" name="twitterWidgetDnt" aria-describedby="twitterWidgetDnt">';
        $html .= '<option value="on" ' . ($this->getValue('twitterWidgetDnt') === 'on' ? 'selected' : '') . '>' . $L->get('Enable') . '</option>';
        $html .= '<option value="off" ' . ($this->getValue('twitterWidgetDnt') === 'off' ? 'selected' : '') . '>' . $L->get('Disable') . '</option>';
        $html .= '</select>';
        $html .= '<small class="form-text text-muted" id="twitterWidgetDnt">' . $L->get('You may choose whether Twitter widgets on your site help to personalize content and suggestions for Twitter users, including ads.') . '</small>';
        $html .= '</div>';

		$html .= '<h4 class="border-bottom border-info text-info">' . $L->get('Google') . '</h4>';
        $html .= '<div class="mb-5">';
        $html .= '<label class="form-label" for="googleVerification">'.$L->get('Google Search console').'</label>';
        $html .= '<input class="form-control" id="googleVerification" name="googleVerification" type="text" aria-describedby="googleVerification" value="'.$this->getValue('googleVerification').'" placeholder="">';
        $html .= '<small class="form-text text-muted" id="googleVerification">' . $L->get('Ownership verification means proving to Search Console that you own a specific website. Site owners in Search Console have access to sensitive Google Search data for a site, and can affect a site\'s presence and behavior on Google Search and other Google services.') . ' <a href="https://support.google.com/webmasters/answer/9008080?hl=en#meta_tag_verification" title="' . $L->get('(More info)') . '">' . $L->get('(More info)') . '</a></small>';
        $html .= '</div>';

        $html .= '<p class="clearfix py-4"> </p>';
        $html .= '<p class="clearfix py-4"> </p>';

		return $html;
	}

	/** Return excerpt from full content of post
	 *
	 * @param string $str Post content
	 * @param int $n Number of characters
	 * @param string $endChar End char append to after excerpt (default "...")
	 * @return string
	 */
	private function content_excerpt( string $str, int $n = 500, string $endChar = '&#8230;' ): string
	{
		$str = strip_tags( $str );
		if ( mb_strlen( $str ) < $n )
		{
	        return $str;
	    }
	    $str = preg_replace('/ {2,}/', ' ', str_replace(["\r", "\n", "\t", "\x0B", "\x0C"], ' ', $str));
	    if ( mb_strlen( $str ) <= $n )
	    {
	        return $str;
	    }
	    $out = '';
	    foreach ( explode( ' ', trim( $str ) ) as $val )
	    {
	        $out .= $val . ' ';
	        if ( mb_strlen( $out ) >= $n )
	        {
	            $out = trim( $out );
	            break;
	        }
	    }
	    return ( mb_strlen( $out ) === mb_strlen( $str ) ) ? $out : $out . $endChar;
	}

}
