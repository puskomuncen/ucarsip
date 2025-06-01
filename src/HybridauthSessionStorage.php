<?php

namespace PHPMaker2025\ucarsip;

use Hybridauth\Exception\RuntimeException;
use Hybridauth\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Hybridauth storage
 */
class HybridauthSessionStorage implements StorageInterface
{
    /**
     * Namespace
     *
     * @var string
     */
    protected $storeNamespace = 'HYBRIDAUTH::STORAGE';

    /**
     * Key prefix
     *
     * @var string
     */
    protected $keyPrefix = '';

    /**
     * Constructor
     */
    public function __construct(protected SessionInterface $session)
    {
        if (!$this->session->has($this->storeNamespace)) {
            $this->session->set($this->storeNamespace, []);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $key = $this->keyPrefix . strtolower($key);
        if ($this->session->has($this->storeNamespace) && isset($this->session->get($this->storeNamespace)[$key])) {
            $value = $this->session->get($this->storeNamespace)[$key];
            if (is_array($value) && array_key_exists('lateObject', $value)) {
                $value = unserialize($value['lateObject']);
            }
            return $value;
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $key = $this->keyPrefix . strtolower($key);
        if (is_object($value)) {
            // We encapsulate as our classes may be defined after session is initialized.
            $value = ['lateObject' => serialize($value)];
        }
        $tmp = $this->session->get($this->storeNamespace);
        $tmp[$key] = $value;
        $this->session->set($this->storeNamespace, $tmp);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->session->set($this->storeNamespace, []);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        $key = $this->keyPrefix . strtolower($key);
        if ($this->session->has($this->storeNamespace) && isset($this->session->get($this->storeNamespace)[$key])) {
            $tmp = $this->session->get($this->storeNamespace);
            unset($tmp[$key]);
            $this->session->set($this->storeNamespace, $tmp);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMatch($key)
    {
        $key = $this->keyPrefix . strtolower($key);
        if ($this->session->has($this->storeNamespace) && count($this->session->get($this->storeNamespace))) {
            $tmp = $this->session->get($this->storeNamespace);
            foreach ($tmp as $k => $v) {
                if (strstr($k, $key)) {
                    unset($tmp[$k]);
                }
            }
            $this->session->set($this->storeNamespace, $tmp);
        }
    }
}
