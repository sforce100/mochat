<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomAutoPull\Listener;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Event\AddContactEvent;
use MoChat\Plugin\RoomAutoPull\Contract\WorkRoomAutoPullContract;
use Psr\Container\ContainerInterface;

/**
 * 新客户打标签监听.
 *
 * @Listener
 */
class MarkTagListener implements ListenerInterface
{
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var WorkContactTagContract
     */
    private $workContactTagService;

    /**
     * @var WorkRoomAutoPullContract
     */
    private $workRoomAutoPullService;

    public function listen(): array
    {
        return [
            AddContactEvent::class,
        ];
    }

    /**
     * @param AddContactEvent $event
     */
    public function process(object $event)
    {
        $contact = $event->message;
        $this->workRoomAutoPullService = $this->container->get(WorkRoomAutoPullContract::class);
        $this->workContactTagService = $this->container->get(WorkContactTagContract::class);

        // 判断是否需要打标签
        if (! $this->isNeedMarkTag($contact)) {
            return;
        }

        // 获取打标签规则
        $tags = $this->getMarkTagRule($contact);
        if (empty($tags)) {
            return;
        }

        // 打标签
        $this->workContactTagService->markTags((int) $contact['corpId'], $contact, $tags);
    }

    /**
     * 判断是否需要打标签.
     *
     * @return bool
     */
    private function isNeedMarkTag(array $contact)
    {
        if (! isset($contact['state']) || empty($contact['state'])) {
            return false;
        }

        $stateArr = explode('-', $contact['State']);
        if ($stateArr[0] !== $this->getStateName()) {
            return false;
        }

        return true;
    }

    /**
     * 获取来源名称.
     *
     * @return string
     */
    private function getStateName()
    {
        return 'workRoomAutoPullId';
    }

    /**
     * 获取打标签规则.
     *
     * @param array $contact 客户
     *
     * @return array[]
     */
    private function getMarkTagRule(array $contact): array
    {
        $stateArr = explode('-', $contact['state']);
        $workRoomAutoPullId = (int) $stateArr[1];

        $data = [];

        $workRoomAutoPull = $this->workRoomAutoPullService->getWorkRoomAutoPullById($workRoomAutoPullId, ['tags']);

        if (empty($workRoomAutoPull) || empty($workRoomAutoPull['tags'])) {
            return $data;
        }

        $tagIds = array_filter(json_decode($workRoomAutoPull['tags'], true));
        $tagList = $this->workContactTagService->getWorkContactTagsById($tagIds, ['id', 'wx_contact_tag_id']);
        empty($tagList) || $data = array_column($tagList, 'wxContactTagId');
        return $data;
    }
}
