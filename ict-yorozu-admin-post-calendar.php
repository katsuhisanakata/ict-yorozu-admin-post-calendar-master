<?php
/*
  Plugin Name: ICTYorozuAdminPostCalendar
  Plugin URI: 
  Description: 投稿のサブメニューにカレンダーを追加。投稿した記事をカレンダー形式で表示。記事のリンクをクリックすると編集画面へ遷移。
  Version: 1.0.0
  Author: ICTよろず相談所
  Author URI: https://ict-yorozu.com/
  License: GPLv2 or later
 */

add_action('init', 'ICTYorozuAdminPostCalendar::init');

class ICTYorozuAdminPostCalendar
{
    const PLUGIN_ID         = 'ict-yorozu-admin-post-calendar';
    const PLUGIN_DOMAIN     = self::PLUGIN_ID;
    
    static function init()
    {
        return new self();
    }
    
    function __construct()
    {
        if (is_admin() && is_user_logged_in()) {
            add_action('admin_menu', [$this, 'set_plugin_sub_menu']);
            load_plugin_textdomain( self::PLUGIN_DOMAIN, false, basename( dirname( __FILE__ ) ).'/languages/' );
        }
    }
    
    function set_plugin_sub_menu()
    {
        add_submenu_page(
			'edit.php',
			__('Calendar', self::PLUGIN_DOMAIN ),
			__('Calendar', self::PLUGIN_DOMAIN ),
			'manage_options',
			'post_calendar',
			[$this, 'add_ict_yorozu_admin_calendar_page'],
			10
		);
    }
    
    function add_ict_yorozu_admin_calendar_page()
	{
		$year = date('Y');
		$month = date('n');
		
		if ( isset($_GET['year']) && $_GET['year'] != '' && isset($_GET['month']) && $_GET['month'] != '' ) :
			if ( preg_match('/^20[0-9]{2}$/', $_GET['year']) && preg_match('/^[0-9]{1,2}$/', $_GET['month']) && checkdate($_GET['month'], 1, $_GET['year'] ) ) :
				$year = $_GET['year'];
				$month = $_GET['month'];
			endif;
		endif;
		
		if ( preg_match('/^20[0-9]{2}$/', $year) && preg_match('/^[0-9]{1,2}$/', $month) && checkdate($month, 1, $year ) ) :
			
			/*
			 * 参考；https://shanabrian.com/web/php_calendar.php	
			 */
			function calendar($year = '', $month = '', $domain) {
			    if (empty($year) && empty($month)) {
			        $year = date('Y');
			        $month = date('n');
			    }
			    
			    $l_day = date('j', mktime(0, 0, 0, $month + 1, 0, $year));
			    
			    $after = date("Y-m-01", mktime(0, 0, 0, $month + 1, 0, $year));
				$before = date("Y-m-d", mktime(0, 0, 0, $month + 1, 0, $year));
			    				
				$arr_result = array();
			    $args = array(
					'posts_per_page'	=> -1,
					'order'				=> 'ASC',
					'orderby'			=> 'date',
					'post_type'			=> 'post',
					'post_status'		=> 'any',
					'date_query' => array(
						'relation' => 'AND',
						array(
							'after'   => $after,
							'compare' => '>=',
						),
						array(
							'before'   => $before,
							'compare' => '<=',
						),
					),
				);
				
				$myposts = get_posts( $args );
				if ( count($myposts) > 0 ) :
					foreach ( $myposts as $post ) : setup_postdata( $post );
						
						$kn_date = new DateTime($post->post_date);
						$pub_date_j = $kn_date->format('j');
						
						array_push( $arr_result, array(
								'id' => $post->ID,
								'pub_title' => $post->post_title,
								'pub_date_j' => $pub_date_j,
								'pub_url' => get_permalink( $post->ID )
							)
						);
						
					endforeach;
				endif;
				wp_reset_postdata();
							    
			    $html = <<<EOM
<style>.prev_current_next{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}.prev_current_next .current{font-weight:bold}table.calendar{width:100%;border-collapse:collapse;box-shadow:0 1px 5px 0 rgba(0,0,0,0.3);border-radius:5px;overflow:hidden}table.calendar thead tr th{padding:6px;background-color:#fff;display:none}table.calendar tbody tr th{padding:6px;background-color:#eee}table.calendar tbody tr th.sun{background-color:#ff4242;color:#fff;border-top-left-radius:5px}table.calendar tbody tr th.sat{background-color:#4ec2fc;color:#fff;border-top-right-radius:5px}table.calendar tbody tr td.sun{background-color:#fff7f7}table.calendar tbody tr td.sat{background-color:#eff9ff}table.calendar tbody tr td.today .date_j{background-color:#23282d;color:#fff}body.admin-color-fresh table.calendar tbody tr td.today .date_j{background-color:#23282d;color:#fff}body.admin-color-light table.calendar tbody tr td.today .date_j{background-color:#e5e5e5;color:#333}body.admin-color-modern table.calendar tbody tr td.today .date_j{background-color:#3858e9;color:#fff}body.admin-color-blue table.calendar tbody tr td.today .date_j{background-color:#096484;color:#fff}body.admin-color-coffee table.calendar tbody tr td.today .date_j{background-color:#c7a589;color:#fff}body.admin-color-ectoplasm table.calendar tbody tr td.today .date_j{background-color:#a3b745;color:#fff}body.admin-color-midnight table.calendar tbody tr td.today .date_j{background-color:#e14d43;color:#fff}body.admin-color-ocean table.calendar tbody tr td.today .date_j{background-color:#9ebaa0;color:#fff}body.admin-color-sunrise table.calendar tbody tr td.today .date_j{background-color:#dd823b;color:#fff}table.calendar tbody tr td{padding:6px;text-align:center;vertical-align:top;width:14.285714285714286%;background-color:#fff;border-right:1px solid #eee;border-bottom:1px solid #eee;box-sizing:border-box}table.calendar tbody tr:last-child td:first-child{border-bottom-left-radius:5px}table.calendar tbody tr:last-child td:last-child{border-bottom-right-radius:5px}table.calendar tbody tr td .date_j{margin:0 auto;border-radius:30px;width:30px;height:30px;line-height:30px;white-space:nowrap}ul.post_list{// background-color:#fff;text-align:left;margin:6px 0 0 18px}ul.post_list li{list-style-type:disc;list-style-position:outside}.information{display:flex;justify-content:space-between;align-items:center;margin:12px 0}@media screen and (max-width:782px){table.calendar thead{border-bottom:1px solid #eee}table.calendar tbody tr th{display:none}table.calendar tbody tr td{display:block;width:100%;border-right:0}table.calendar tbody tr td.blank{display:none}}</style>
<table class="calendar"><thead><tr><th colspan="7">{$year}年{$month}月</th></tr></thead><tbody><tr><th class="sun">日</th><th>月</th><th>火</th><th>水</th><th>木</th><th>金</th><th class="sat">土</th></tr>
EOM;
			    $lc = 0;
			 
			    // 月末分繰り返す
			    for ($i = 1; $i < $l_day + 1;$i++) {
			        $classes = array();
			        $class   = '';
			 
			        // 曜日の取得
			        $week = date('w', mktime(0, 0, 0, $month, $i, $year));
			 
			        // 曜日が日曜日の場合
			        if ($week == 0) {
			            $html .= "<tr>";
			            $lc++;
			        }
			 
			        // 1日の場合
			        if ($i == 1) {
			            if($week != 0) {
			                $html .= "<tr>";
			                $lc++;
			            }
			            $html .= repeatEmptyTd($week);
			        }
			 
			        if ($week == 6) {
			            $classes[] = 'sat';
			        } else if ($week == 0) {
			            $classes[] = 'sun';
			        }
			 
			        if ($i == date('j') && $year == date('Y') && $month == date('n')) {
			            // 現在の日付の場合
			            $classes[] = 'today';
			        }
			 
			        if (count($classes) > 0) {
			            $class = ' class="'.implode(' ', $classes).'"';
			        }
			 
			        $html .= '<td'.$class.'><div class="date_j">' . $i . '</div>';
			        
			        $post_list_count = 0;
			        
			        foreach ( $arr_result as $result ) :
						if ( (int)$result['pub_date_j'] === $i ) :
							if ( $post_list_count === 0 ) :
								$html .= '<ul class="post_list">';
							endif;
							$html .= '<li>';
							$html .= '<a href="' . home_url('/wp-admin/post.php?post=') . $result['id'] . '&action=edit" style="margin-right:12px">'; 
							$html .= $result['pub_title'];
							$html .= '</a>';
							$html .= '<a href="' . esc_url( $result['pub_url'] ) . '" target="_blank">'; 
							$html .= __('View', $domain);
							$html .= '</a>';
							$html .= '</li>';
							$post_list_count++;
						endif;
					endforeach;
					
					if ( $post_list_count > 0 ) :
						$html .= '</ul>';
						$post_list_count = 0;
					endif;
					
			        $html .= '</td>';
			 
			        // 月末の場合
			        if ($i == $l_day) {
			            $html .= repeatEmptyTd(6 - $week);
			        }
			        // 土曜日の場合
			        if ($week == 6) {
			            $html .= "</tr>";
			        }
			    }
			 
			    if ($lc < 6) {
			        $html .= "<tr>";
			        $html .= repeatEmptyTd(7);
			        $html .= "</tr>";
			    }
			 
			    if ($lc == 4) {
			        $html .= "<tr>";
			        $html .= repeatEmptyTd(7);
			        $html .= "</tr>";
			    }
			 
			    $html .= "</tbody>";
			    $html .= "</table>";
			 
			    return $html;
			}
			
			function repeatEmptyTd($n = 0) {
			    return str_repeat('<td class="blank"><div class="date_j">&nbsp;</div></td>', $n);
			}
		?>
		<div class="wrap">
		    <div class="metabox-holder">
			    <div class="prev_current_next">
				    <div class="prev"><a href="<?php echo esc_url( home_url( '/' ) ); ?>wp-admin/edit.php?page=post_calendar&year=<?php echo date("Y", mktime(0, 0, 0, $month - 1, 1, $year)); ?>&month=<?php echo date("n", mktime(0, 0, 0, $month - 1, 1, $year)); ?>"><?php echo date_i18n( __( date("Y月n日", mktime(0, 0, 0, $month - 1, 1, $year)) ), self::PLUGIN_DOMAIN ); ?></a></div>
						<div class="current"><?php echo date_i18n( __($year . '月' . $month . '日'), self::PLUGIN_DOMAIN );?></div>
					<div class="next"><a href="<?php echo esc_url( home_url( '/' ) ); ?>wp-admin/edit.php?page=post_calendar&year=<?php echo date("Y", mktime(0, 0, 0, $month + 1, 1, $year)); ?>&month=<?php echo date("n", mktime(0, 0, 0, $month + 1, 1, $year)); ?>"><?php echo date_i18n( __( date("Y月n日", mktime(0, 0, 0, $month + 1, 1, $year)) ), self::PLUGIN_DOMAIN ); ?></a></div>
			    </div>
				<?php echo calendar($year, $month, self::PLUGIN_DOMAIN); ?>
				<div class="information">
					<div class="to_today"><a href="<?php echo esc_url( home_url( '/' ) ); ?>wp-admin/edit.php?page=post_calendar"><?php _e('Today', self::PLUGIN_DOMAIN); ?></a></div>
				</div>
		    </div>
		</div>
		<?php
		endif; // preg_match('/^20[0-9]{2}$/', $year) && preg_match('/^[0-9]{1,2}$/', $month) && checkdate($month, 1, $year )
	}
}