<?php

    class UserManager {
        private $users;
        private $directory;
        private $filename;

        public function __construct() {
            $this->users = array();
            $this->directory = __DIR__.'/config';
            $this->filename = 'users';

            $filepath = $this->directory.'/'.$this->filename;

            if (!is_dir($this->directory)) {
                mkdir($this->directory);
            }
            if (!is_file($filepath)) {
                $this->persistUsers();
            }

            $this->users = $this->getFile($filepath);
        }


        /**
         * Permet de récupérer un utilisateur par son pseudo
         *
         * @param $pseudo
         *
         * @return User|null
         */
        public function findUser($pseudo) {
            foreach ($this->users as $user) {
                if ($user->getPseudo() == $pseudo) {
                    return $user;
                }
            }

            return null;
        }

        /**
         * Permet d'ajouter un utilisateur à l'array $users
         *
         * @param User $user
         */
        public function addUser(User $user) {
            $user->setId(count($this->users));
            $this->users[] = $user;
        }

        /**
         * Permet de modifier un utilisateur de l'array $users
         *
         * @param User $user
         */
        public function editUser(User $user) {
            $this->users[$user->getId()] = $user;
        }

        /**
         * Permet de supprimer un utilisateur de l'array $users
         *
         * @param User $user
         */
        public function removeUser(User $user) {
            unset($this->users[$user->getId()]);
        }

        /**
         * Retourne de récupérer l'array $users
         *
         * @return array
         */
        public function getUsers() {
            return $this->users;
        }

        /**
         * Permet de créer le fichier stockant l'array $users
         *
         * @throws Exception
         */
        private function persistUsers() {
            $filepath = $this->directory.'/'.$this->filename;

            if (is_file($filepath)) {
                throw new Exception('Le fichier "'. $filepath .' existe deja...');
            }

            $this->saveFile($filepath, serialize($this->users));
        }

        /**
         * Permet d'enregister dans le fichier l'array $users
         */
        public function updateUsers() {
            $filepath = $this->directory.'/'.$this->filename;

            if (!is_file($filepath)) {
                $this->persistUsers();
                return;
            }

            $this->saveFile($filepath, serialize($this->users));
        }

        /**
         * Permet de supprimer le fichier stockant les utilisateurs
         *
         * @throws Exception
         */
        private function deleteUsers() {
            $filepath = $this->directory.'/'.$this->filename;

            if (!is_file($filepath)) {
                throw new Exception('Le fichier "'. $filepath .' n\'existe pas...');
            }

            unlink($filepath);
        }

        /**
         * Ecrit dans le fichier spécifié par $filepath
         *
         * @param $filepath
         * @param $string
         */
        private function saveFile($filepath, $string) {
            $file = fopen($filepath, 'w+');
            fwrite($file, $string);
            fclose($file);
        }

        /**
         * Renvoie le contenu d'un fichier sous forme d'array
         *
         * @param $filepath
         * @return array
         */
        private function getFile($filepath) {
            return unserialize(file_get_contents($filepath));
        }


    }

    class User {
        protected $id;
        protected $pseudo;
        protected $email;
        protected $cryptedPassword;
        protected $salt;
        protected $role;

        protected $timestampCreation;
        protected $lastLogin;

        public function __construct() {
            $this->timestampCreation = time();
            $this->lastLogin = time();
        }

        /**
         * Remplit un objet User à partir de la requête
         *
         * @return Post
         */
        public function handlePostRequest() {
            $this->pseudo = htmlentities($_POST['user_pseudo']);
            $this->email = htmlentities($_POST['user_email']);
            $this->salt = $this->generateSalt();
            $this->cryptedPassword = sha1($this->salt.$_POST['user_password']);
            $this->role = $_POST['user_role'];
        }

        /**
         * Retourne une array composé d'un boolean de validaté et d'une array des toutes les erreurs rencontrées
         *
         * @return array
         */
        public function validate() {
            $validation = array(
                'valid' => true,
                'errors' => array()
            );

            if ($this->pseudo == '') {
                $validation['valid'] = false;
                $validation['errors']['user_pseudo'] = 'Votre pseudo ne peut être vide';
            }
            if ($this->email == '') {
                $validation['valid'] = false;
                $validation['errors']['user_email'] = 'Vous devez spécifier votre email';
            }
            if ($this->cryptedPassword == sha1($this->salt)) {
                $validation['valid'] = false;
                $validation['errors']['user_password'] = 'Vos devez spécifier un mot de passe';
            }

            return $validation;
        }

        /**
         * Permet de générer une valeur aléatoire pour le cryptage du mot de passe
         *
         * @return string
         */
        private function generateSalt() {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $salt = '';
            for ($i = 0; $i < 7; $i++) {
                $salt .= $characters[rand(0, $charactersLength - 1)];
            }
            return $salt;
        }

        /**
         * Méthode rapide pour verifier le role d'un utilisateur
         *
         * @param $role
         *
         * @return bool
         */
        public function is($role) {
            if ($this->role == $role) {
                return true;
            }

            return false;
        }

        /**
         * @return mixed
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * @param mixed $id
         *
         * @return User
         */
        public function setId($id)
        {
            $this->id = $id;
            return $this;
        }

        /**
         * @return mixed
         */
        public function getPseudo()
        {
            return $this->pseudo;
        }

        /**
         * @param mixed $pseudo
         *
         * @return User
         */
        public function setPseudo($pseudo)
        {
            $this->pseudo = $pseudo;
            return $this;
        }

        /**
         * @return mixed
         */
        public function getEmail()
        {
            return $this->email;
        }

        /**
         * @param mixed $email
         *
         * @return User
         */
        public function setEmail($email)
        {
            $this->email = $email;
            return $this;
        }

        /**
         * @return mixed
         */
        public function getCryptedPassword()
        {
            return $this->cryptedPassword;
        }

        /**
         * @param mixed $cryptedPassword
         *
         * @return User
         */
        public function setCryptedPassword($cryptedPassword)
        {
            $this->cryptedPassword = $cryptedPassword;
            return $this;
        }

        /**
         * @return mixed
         */
        public function getSalt()
        {
            return $this->salt;
        }

        /**
         * @return mixed
         */
        public function getRole()
        {
            return $this->role;
        }

        /**
         * @param mixed $role
         *
         * @return User
         */
        public function setRole($role)
        {
            $this->role = $role;
            return $this;
        }

        /**
         * @return mixed
         */
        public function getTimestampCreation()
        {
            return $this->timestampCreation;
        }

        /**
         * @return mixed
         */
        public function getLastLogin()
        {
            return $this->lastLogin;
        }

        /**
         * @param mixed $lastLogin
         *
         * @return User
         */
        public function setLastLogin($lastLogin)
        {
            $this->lastLogin = $lastLogin;
            return $this;
        }

    }