<?php
/*
 * Plugin Name: Group Tabs for BuddyPress Members
 * Plugin URI: http://wordpress.lowtone.nl/buddypress-members-grouptabs
 * Description: Add tabs for groups to the BuddyPress Members page.
 * Version: 1.0
 * Author: Lowtone <info@lowtone.nl>
 * Author URI: http://lowtone.nl
 * License: http://wordpress.lowtone.nl/license
 */
/**
 * @author Paul van der Meijs <code@lowtone.nl>
 * @copyright Copyright (c) 2011-2012, Paul van der Meijs
 * @license http://wordpress.lowtone.nl/license/
 * @version 1.0
 * @package wordpress\plugins\lowtone\buddypress\members\grouptabs
 */

namespace lowtone\buddypress\members\grouptabs {

	use lowtone\net\URL;

	add_filter("bp_ajax_querystring", function($query, $object) {
		if ("members" != $object)
			return $query;

		if (is_null($scope = $_REQUEST["scope"] ?: $_COOKIE["bp-members-scope"]))
			return $query;

		if (!preg_match("#group_(\d+)#", $scope, $matches))
			return $query;

		$group_id = $matches[1];

		if (!is_numeric($group_id))
			return $query;

		if (!is_array($query))
			parse_str($query, $query);

		$include = $query["include"];

		if (is_string("include"))
			$include = explode(",", $include);

		global $wpdb, $bp;

		$q = "SELECT user_id FROM " . $bp->groups->table_name_members . " WHERE `is_confirmed`=1 AND `group_id`=%d";

		if (is_null($result = $wpdb->get_results($wpdb->prepare($q, (int) $group_id))))
			return $query;

		$query["include"] = implode(",", array_filter(array_unique(array_merge($include, array_map(function($row) {return $row->user_id;}, $result)))));

		return $query;
	}, 9999, 2);

	add_action("bp_members_directory_member_types", function() {
		bp_has_groups();

		while (bp_groups()) {
			bp_the_group();
			
			echo sprintf('<li id="members-group_%s" class="members-group"><a href="%s">%s <span>%s</span></a></li>', $GLOBALS["groups_template"]->group->id, URL::fromString(bp_get_members_directory_permalink())->fragment(bp_get_group_slug()), bp_get_group_name(), bp_get_group_total_members());
		} 
	});

}