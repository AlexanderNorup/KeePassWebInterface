<?php

use \KeePassPHP\iFilter as iFilter;
use \KeePassPHP\Entry as Entry;
use \KeePassPHP\Group as Group;
class DatabaseFilterForIndex implements iFilter
{
    public function acceptEntry(Entry $entry)
    {
        return true;
    }

    public function acceptGroup(Group $group)
    {
        return true;
    }

    public function acceptHistoryEntry(Entry $historyEntry)
    {
        return false;
    }

    public function acceptTags()
    {
        return false;
    }

    public function acceptIcons()
    {
        return true;
    }

    public function acceptPasswords()
    {
        return false;
    }

    public function acceptStrings($key)
    {
        $acceptedStrings = array(
            "Title",
            "UserName"
        );
        return in_array($key, $acceptedStrings);
    }
}
