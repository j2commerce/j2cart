<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2storeTableCoupon extends F0FTable
{
    /**
     * Override store method to ensure NULL values are stored in database
     * instead of '0000-00-00 00:00:00'
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null
     *
     * @return  boolean  True on success
     */
    public function store($updateNulls = false)
    {
        $db = $this->getDbo();
        $nullDate = $db->getNullDate();

        // Convert '0000-00-00 00:00:00' to null before storing
        if (isset($this->valid_from) && ($this->valid_from === $nullDate || $this->valid_from === '0000-00-00 00:00:00' || empty($this->valid_from))) {
            $this->valid_from = null;
        }
        if (isset($this->valid_to) && ($this->valid_to === $nullDate || $this->valid_to === '0000-00-00 00:00:00' || empty($this->valid_to))) {
            $this->valid_to = null;
        }

        // Force update nulls to true so NULL values are actually stored
        return parent::store(true);
    }

	/**
	 * Copy (duplicate) one or more records
	 *
	 * @param   integer|array  $cid  The primary key value (or values) or the record(s) to copy
	 *
	 * @return  boolean  True on success
	 */
	public function copy($cid = null)
	{
		//We have to cast the id as array, or the helper function will return an empty set
		if($cid)
		{
			$cid = (array) $cid;
		}

		F0FUtilsArray::toInteger($cid);
		$k = $this->_tbl_key;

		if (count($cid) < 1)
		{
			if ($this->$k)
			{
				$cid = array($this->$k);
			}
			else
			{
				$this->setError("No items selected.");

				return false;
			}
		}

		$created_by  = $this->getColumnAlias('created_by');
		$created_on  = $this->getColumnAlias('created_on');
		$modified_by = $this->getColumnAlias('modified_by');
		$modified_on = $this->getColumnAlias('modified_on');
		$coupon_name = $this->getColumnAlias('coupon_name');
		$coupon_code = $this->getColumnAlias('coupon_code');
		$locked_byName = $this->getColumnAlias('locked_by');
		$checkin       = in_array($locked_byName, $this->getKnownFields());

		foreach ($cid as $item)
		{
			// Prevent load with id = 0

			if (!$item)
			{
				continue;
			}

			$this->load($item);

			if ($checkin)
			{
				// We're using the checkin and the record is used by someone else

				if ($this->isCheckedOut($item))
				{
					continue;
				}
			}

			if (!$this->onBeforeCopy($item))
			{
				continue;
			}

			$this->$k           = null;
			$this->$coupon_name = '(copy)'.$this->$coupon_name;
			$this->$coupon_code = '(copy)'.$this->$coupon_code;
			$this->$created_by  = null;
			$this->$created_on  = null;
			$this->$modified_on = null;
			$this->$modified_by = null;

			// Let's fire the event only if everything is ok
			if ($this->store())
			{
				$this->onAfterCopy($item);
			}

			$this->reset();
		}

		return true;
	}
}
