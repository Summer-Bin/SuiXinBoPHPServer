<?php
/**
 * 房间信息上报接口
 * Date: 2016/11/17
 */
require_once dirname(__FILE__) . '/../../Path.php';

require_once SERVICE_PATH . '/TokenCmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/NewLiveRecord.php';


class ReportLiveRoomInfoCmd extends TokenCmd
{
    //NewLiveRecord
    private $record;

    public function parseInput()
    {
        $liveRecord = new NewLiveRecord();
        $req = $this->req;

		$room = $req['room'];
        // room-必填
        if (!isset($req['room']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of room.');
        }
		$room = $req['room'];
		
        // 检查title-选填
        if (isset($room['title']) && is_string($room['title']))
        {
            if (strlen($room['title']) > 128)
            {
                return new CmdResp(ERR_REQ_DATA, 'Title length too long.');
            }
            $liveRecord->setTitle($room['title']);
        }
		
        // 检查封面-选填
        if (isset($room['cover']) && is_string($room['cover']))
        {
            if (strlen($room['cover']) > 128)
            {
                return new CmdResp(ERR_REQ_DATA, 'Cover length too long.');
            }
            $liveRecord->setCover($room['cover']);
        }

		// 检查type-必填
        if (!isset($room['type']))
		{
            return new CmdResp(ERR_REQ_DATA, 'Lack of type.');
		}	
		if(!is_string($room['type']))
        {
            return new CmdResp(ERR_REQ_DATA, 'invalid type.');
        }
		$liveRecord->setRoomType($room['type']);
		
		// 检查 av room id-必填
        if (!isset($room['roomnum']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of av room id.');
        }
        if ($room['roomnum'] !== (int)$room['roomnum'])
        {
            return new CmdResp(ERR_REQ_DATA, 'AV room id should be integer.');
        }
        $liveRecord->setAvRoomId($room['roomnum']);

        // 检查 chat room id-必填
        if (!isset($room['groupid']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of chat room id.');
        }
        if (!is_string($room['groupid']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Chat room id should be string.');
        }
        $liveRecord->setChatRoomId($room['groupid']);

        if (!isset($room['appid']) && !is_int($room['appid']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of appid.');
        }
		$liveRecord->setAppid($room['appid']);
	
        // LBS-选填
        if (isset($req['lbs']) && is_array($req['lbs']))
        {
            $lbsInReq = $req['lbs'];
            if (isset($lbsInReq['longitude']) && is_double($lbsInReq['longitude']))
            {
                $liveRecord->setLongitude($lbsInReq['longitude']);
            }
            if (isset($lbsInReq['latitude']) && is_double($lbsInReq['latitude']))
            {
                $liveRecord->setLatitude($lbsInReq['latitude']);
            }
            if (isset($lbsInReq['address']) && is_string($lbsInReq['address']))
            {
                if (strlen($lbsInReq['address']) > 100)
                {
                    return new CmdResp(ERR_REQ_DATA, 'Address length too long.');
                }
                $liveRecord->setAddress($lbsInReq['address']);
            }
        }
        	
        $liveRecord->setHostUid($this->user);
		$liveRecord->setHostUserName($this->user);
        $this->record = $liveRecord;

        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        $id = $this->record->save();
        if ($id < 0)
        {
            return new CmdResp(ERR_SERVER, 'Server internal error');
        }
        return new CmdResp(ERR_SUCCESS, '');
    }	
}