# Rudra-Auth
Авторизация

Пример метода Авторизации

    public function login()
    {
        $v      = $this->getDi()->get('validation');
        $result = [
            'csrf' => $v->sanitize($this->getDi()->getPost('csrf'))->csrf()->v(),
            'name' => $v->sanitize($this->getDi()->getPost('name'))->required('Fill out a form :: name')->v(),
            'pass' => $v->sanitize($this->getDi()->getPost('pass'))->required('Fill out a form :: pass')->hash(Config::SALT, 10)->v()
        ];

        if ($v->access($result)) {
            $this->getDi()->get('auth')->login($v->get($result, ['csrf']), $this->getDi()->get('notice')->noticeErrorMessage('Указаны неверные данные'));
        } else {
            foreach ($v->flash($result, ['csrf']) as $key => $message) {
                $this->getDi()->setSubSession('alert', $key, $this->getDi()->get('notice')->noticeErrorMessage($message));
            }
            $this->getDi()->setSubSession('value', 'name', $v->get($result, ['csrf'])['name']);
            $this->getDi()->get('redirect')->run('login');
        }
    }
