<?php

use \KeePassPHP\iFilter as iFilter;
use \KeePassPHP\Entry as Entry;
use \KeePassPHP\Group as Group;
class DatabaseFilterWithEverything implements iFilter
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
        return true;
    }

    public function acceptIcons()
    {
        return true;
    }

    public function acceptPasswords()
    {
        return true;
    }

    public function acceptStrings($key)
    {
        return true;
    }
}
