<?php
class Table_GuestUserToken extends Omeka_Db_Table
{
    public function findByToken($token)
    {
        $select = $this->getSelect();
        $select->where('token = ?', $token);
        return $this->fetchObject($select);
    }
}
