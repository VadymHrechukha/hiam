<?php


namespace hiam\tests\_support\Helper;


use hiam\tests\_support\AcceptanceTester;
use yii\base\InvalidArgumentException;

trait UserSessionTrait
{
    /**
     * Path to session cache in docker environment
     * @var string
     */
    protected string $sessionsPath = '/tmp';

    /**
     * @param AcceptanceTester $I
     * @return array<string, array>
     */
    protected function getUsersSession(AcceptanceTester $I): array
    {
        $cookie = $I->grabCookie(ini_get('session.name'));
        $sessionName = 'sess_' . $cookie;
        $sessionFile = file_get_contents($this->sessionsPath . '/' . $sessionName);

        return [$sessionName => $this->sessionUnseriazlize($sessionFile)];
    }

    /**
     * @return array<string, array>
     */
    protected function getAllSessions(): array
    {
        $directory = new \DirectoryIterator($this->sessionsPath);
        $regex = new \RegexIterator($directory, '/^sess_.*$/', \RegexIterator::GET_MATCH);

        $sessions = [];
        foreach ($regex as $item) {
            $sessionName = reset($item);
            $sessionFile = file_get_contents($this->sessionsPath . '/' . $sessionName);
            $sessions[$sessionName] = $this->sessionUnseriazlize($sessionFile);
        }

        return $sessions;
    }

    /**
     * @param string $session_data
     * @return array
     */
    private function sessionUnseriazlize(string $session_data): array
    {
        $return_data = [];
        $offset = 0;
        while ($offset < strlen($session_data)) {
            if (!strstr(substr($session_data, $offset), "|")) {
                throw new InvalidArgumentException("invalid data, remaining: " . substr($session_data, $offset));
            }
            $pos = strpos($session_data, "|", $offset);
            $num = $pos - $offset;
            $varname = substr($session_data, $offset, $num);
            $offset += $num + 1;
            $data = unserialize(substr($session_data, $offset));
            $return_data[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $return_data;
    }
}