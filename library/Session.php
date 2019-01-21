<?php

namespace payla\library;

use Payla;

class Session {
	public $data = [];
	public $flashParam = '__flash';

	public function init() {
		if (!session_id()) {
			ini_set('session.use_only_cookies', 'On');
			ini_set('session.use_trans_sid', 'Off');
			ini_set('session.cookie_httponly', 'On');

			session_set_cookie_params(0, '/');
			session_start();

			$this->updateFlashCounters();
		}

		$this->data =& $_SESSION;
	}

	public function clear(){
		$this->data = [];
	}

	public function getId() {
		return session_id();
	}

	public function destroy() {
		return session_destroy();
	}

	public function get($key, $defaultValue = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $defaultValue;
    }

    protected function updateFlashCounters()
    {
        $counters = $this->get($this->flashParam, []);
        if (is_array($counters)) {
            foreach ($counters as $key => $count) {
                if ($count > 0) {
                    unset($counters[$key], $this->data[$key]);
                } elseif ($count == 0) {
                    $counters[$key]++;
                }
            }
            $this->data[$this->flashParam] = $counters;
        } else {
            // fix the unexpected problem that flashParam doesn't return an array
            unset($this->data[$this->flashParam]);
        }
    }

	public function setFlash($key, $value = true, $removeAfterAccess = true)
    {
        $counters = $this->get($this->flashParam, []);
        $counters[$key] = $removeAfterAccess ? -1 : 0;
        $this->data[$key] = $value;
        $this->data[$this->flashParam] = $counters;
    }

    public function getFlash($key, $defaultValue = null, $delete = true)
    {
        $counters = $this->get($this->flashParam, []);
        if (isset($counters[$key])) {
            $value = $this->get($key, $defaultValue);
            if ($delete) {
                $this->removeFlash($key);
            } elseif ($counters[$key] < 0) {
                // mark for deletion in the next request
                $counters[$key] = 1;
                $this->data[$this->flashParam] = $counters;
            }
            return $value;
        }
        return $defaultValue;
    }

	public function removeFlash($key)
    {
        $counters = $this->get($this->flashParam, []);
        $value = isset($this->data[$key], $counters[$key]) ? $this->data[$key] : null;
        unset($counters[$key], $this->data[$key]);
        $this->data[$this->flashParam] = $counters;
        return $value;
    }

    public function removeAllFlashes()
    {
        $counters = $this->get($this->flashParam, []);
        foreach (array_keys($counters) as $key) {
            unset($this->data[$key]);
        }
        unset($this->data[$this->flashParam]);
    }

    public function hasFlash($key)
    {
        return $this->getFlash($key, null, false) !== null;
    }
}