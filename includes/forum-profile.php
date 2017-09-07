<?php

if (!defined('ABSPATH')) exit;

class AsgarosForumProfile {
    protected static $instance = null;
    private $asgarosforum = null;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    protected function __clone() {}

    protected function __construct() {
        global $asgarosforum;

        $this->asgarosforum = $asgarosforum;
    }

    // Check if the profile functionality is enabled.
    public function functionalityEnabled() {
        return $this->asgarosforum->options['enable_profiles'];
    }

    // Sets the current view when the functionality is enabled.
    public function setCurrentView() {
        if ($this->functionalityEnabled()) {
            $this->asgarosforum->current_view = 'profile';
        } else {
            $this->asgarosforum->current_view = 'overview';
        }
    }

    private function getUserData($userID = false) {
        if (!$userID && !empty($_GET['id'])) {
            $userID = absint($_GET['id']);
        }

        return get_user_by('id', $userID);
    }

    // Sets the current title.
    public function setCurrentTitle() {
        $userData = $this->getUserData();

        if ($userData) {
            $this->asgarosforum->current_title = __('Profile', 'asgaros-forum').': '.$userData->display_name;
        } else {
            $this->asgarosforum->current_title = __('Profile', 'asgaros-forum');
        }
    }

    // Shows the profile of a user.
    public function showProfile($userID = false) {
        $userData = $this->getUserData($userID);

        if ($userData) {
            $showAvatars = get_option('show_avatars');
            $userOnline = (AsgarosForumOnline::isUserOnline($userData->ID)) ? ' class="user-online"' : '';

            echo '<div id="forum-profile"'.$userOnline.'>';

            if ($showAvatars) {
                echo get_avatar($userData->ID, 180);
            }

            // Show display name.
            echo '<div class="display-name">';
                echo $userData->display_name;
            echo '</div>';

            // Show forum role.
            $cellTitle = __('Forum Role:', 'asgaros-forum');
            $cellValue = __('User', 'asgaros-forum');

            if (AsgarosForumPermissions::isAdministrator($userData->ID)) {
                $cellValue = __('Administrator', 'asgaros-forum');
            } else if (AsgarosForumPermissions::isModerator($userData->ID)) {
                $cellValue = __('Moderator', 'asgaros-forum');
            } else if (AsgarosForumPermissions::isBanned($userData->ID)) {
                $cellValue = __('Banned', 'asgaros-forum');
            }

            $this->renderProfileRow($cellTitle, $cellValue);

            // Show first name.
            if (!empty($userData->first_name)) {
                $cellTitle = __('First Name:', 'asgaros-forum');
                $cellValue = $userData->first_name;

                $this->renderProfileRow($cellTitle, $cellValue);
            }

            // Show website.
            if (!empty($userData->user_url)) {
                $cellTitle = __('Website:', 'asgaros-forum');
                $cellValue = '<a href="'.$userData->user_url.'" target="_blank">'.$userData->user_url.'</a>';

                $this->renderProfileRow($cellTitle, $cellValue);
            }

            // Show member since.
            $cellTitle = __('Member Since:', 'asgaros-forum');
            $cellValue = $this->asgarosforum->format_date($userData->user_registered, false);

            $this->renderProfileRow($cellTitle, $cellValue);

            // Show topics started.
            $createdTopics = $this->asgarosforum->getTopicsByUser($userData->ID);
            $counterTopics = count($createdTopics);
            $cellTitle = __('Topics Started:', 'asgaros-forum');
            $cellValue = number_format_i18n($counterTopics);

            $this->renderProfileRow($cellTitle, $cellValue);

            // Show replies created.
            $createdPosts = $this->asgarosforum->countPostsByUser($userData->ID);
            $counterPosts = $createdPosts - $counterTopics;
            $cellTitle = __('Replies Created:', 'asgaros-forum');
            $cellValue = number_format_i18n($counterPosts);

            $this->renderProfileRow($cellTitle, $cellValue);

            echo '<div class="clear"></div>';

            echo '</div>';
        } else {
            _e('This user does not exist.', 'asgaros-forum');
        }
    }

    public function renderProfileRow($cellTitle, $cellValue) {
        echo '<div>';
            echo '<span>'.$cellTitle.'</span>';
            echo '<span>'.$cellValue.'</span>';
        echo '</div>';
    }
}
