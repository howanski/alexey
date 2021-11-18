<?php

declare(strict_types=1);

namespace App\Class;

use App\Entity\User;
use App\Service\SimpleSettingsService;

final class MoneyNodeSettings
{
    private const PREFIX = 'MONEY_NODE_GROUP_NAME_';
    public const GROUPS_MAX = 10;

    private User $user;

    public $name0;
    public $name1;
    public $name2;
    public $name3;
    public $name4;
    public $name5;
    public $name6;
    public $name7;
    public $name8;
    public $name9;

    public function __construct(User $user)
    {
        $this->user = $user;
        for ($i = 0; $i < self::GROUPS_MAX; $i++) {
            $this->{'name' . $i} = '';
        }
    }

    public function selfConfigure(SimpleSettingsService $simpleSettingsService): void
    {
        $conf = [];
        for ($i = 0; $i < self::GROUPS_MAX; $i++) {
            $conf[] = self::PREFIX . $i;
        }
        $namesRead = $simpleSettingsService->getSettings(keys: $conf, user: $this->user);
        foreach ($namesRead as $key => $val) {
            $id = str_replace(search: self::PREFIX, replace: '', subject: $key);
            $this->{'name' . $id} = $val;
        }
    }

    public function selfPersist(SimpleSettingsService $simpleSettingsService): void
    {
        $conf = [];
        for ($i = 0; $i < self::GROUPS_MAX; $i++) {
            $conf[self::PREFIX . $i] = strval($this->{'name' . $i});
        }
        $simpleSettingsService->saveSettings(settings: $conf, user: $this->user);
    }

    public function getChoices(): array
    {
        $choices = [];
        for ($i = 0; $i < self::GROUPS_MAX; $i++) {
            $val = strval($this->{'name' . $i});
            if (strlen($val) > 0) {
                $choices[$val] = $i;
            }
        }
        if (count($choices) === 0) {
            $choices['---'] = 0;
        }
        ksort($choices);
        return $choices;
    }

    public function getGroupName(int $groupId): string
    {
        $val = strval($this->{'name' . $groupId});
        if (strlen($val) === 0) {
            $val = '---';
        }
        return $val;
    }
}
