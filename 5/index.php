<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

function getFormData($field) {
    return $_COOKIE["form_$field"] ?? '';
}

function setFormCookie($name, $value, $expire = 0) {
    setcookie("form_$name", $value, $expire, '/');
}

function setErrorCookie($name, $message) {
    setcookie("error_$name", $message, 0, '/');
}

function generateLogin($fio) {
    $clean = preg_replace('/[^a-zA-Zа-яА-Я]/u', '', $fio);
    $clean = substr($clean, 0, 10);
    return strtolower($clean) . rand(100, 999);
}


function generatePassword($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    return substr(str_shuffle($chars), 0, $length);
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_action'])) {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($login) || empty($password)) {
        setErrorCookie('auth', 'Заполните логин и пароль');
        header('Location: index.php');
        exit();
    }
    
    $user = 'u82392';
    $pass = '1685352';
    $dbname = 'u82392';
    
    try {
        $db = new PDO("mysql:host=localhost;dbname=$dbname", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $stmt = $db->prepare("SELECT u.*, a.* FROM users u 
                              JOIN applications a ON u.application_id = a.id 
                              WHERE u.login = ?");
        $stmt->execute([$login]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData && password_verify($password, $userData['password_hash'])) {
            $_SESSION['user_id'] = $userData['application_id'];
            $_SESSION['user_login'] = $userData['login'];
            setErrorCookie('auth', ''); 
            header('Location: index.php');
            exit();
        } else {
            setErrorCookie('auth', 'Неверный логин или пароль');
            header('Location: index.php');
            exit();
        }
    } catch (PDOException $e) {
        setErrorCookie('auth', 'Ошибка базы данных');
        header('Location: index.php');
        exit();
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['login_action'])) {
    $errors = [];
    $allowedLanguages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskel', 'Clojure', 'Prolog', 'Scala', 'Go'];

    if (empty($_POST['fio'])) {
        $errors['fio'] = 'Заполните ФИО.';
        setErrorCookie('fio', $errors['fio']);
    } elseif (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s]+$/u', $_POST['fio'])) {
        $errors['fio'] = 'Допустимы только буквы и пробелы';
        setErrorCookie('fio', $errors['fio']);
    } elseif (strlen($_POST['fio']) > 150) {
        $errors['fio'] = 'Не более 150 символов';
        setErrorCookie('fio', $errors['fio']);
    }
    setFormCookie('fio', $_POST['fio']);

    if (empty($_POST['phone'])) {
        $errors['phone'] = 'Заполните телефон.';
        setErrorCookie('phone', $errors['phone']);
    } elseif (!preg_match('/^\+?\d{10,15}$/', $_POST['phone'])) {
        $errors['phone'] = 'От 10 до 15 цифр, можно начинать с +';
        setErrorCookie('phone', $errors['phone']);
    }
    setFormCookie('phone', $_POST['phone']);

    if (empty($_POST['email'])) {
        $errors['email'] = 'Заполните email.';
        setErrorCookie('email', $errors['email']);
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Некорректный email';
        setErrorCookie('email', $errors['email']);
    }
    setFormCookie('email', $_POST['email']);

    if (empty($_POST['birthdate'])) {
        $errors['birthdate'] = 'Укажите дату рождения';
        setErrorCookie('birthdate', $errors['birthdate']);
    } else {
        $birthdate = DateTime::createFromFormat('Y-m-d', $_POST['birthdate']);
        $today = new DateTime();
        $minAge = new DateTime('-150 years');
        if (!$birthdate || $birthdate > $today || $birthdate < $minAge) {
            $errors['birthdate'] = 'Некорректная дата';
            setErrorCookie('birthdate', $errors['birthdate']);
        }
    }
    setFormCookie('birthdate', $_POST['birthdate']);

    if (empty($_POST['gender'])) {
        $errors['gender'] = 'Укажите пол';
        setErrorCookie('gender', $errors['gender']);
    } elseif (!in_array($_POST['gender'], ['male', 'female'])) {
        $errors['gender'] = 'Выберите из списка';
        setErrorCookie('gender', $errors['gender']);
    }
    setFormCookie('gender', $_POST['gender']);

    if (empty($_POST['languages'])) {
        $errors['languages'] = 'Выберите хотя бы один язык';
        setErrorCookie('languages', $errors['languages']);
    } else {
        foreach ($_POST['languages'] as $lang) {
            if (!in_array($lang, $allowedLanguages)) {
                $errors['languages'] = 'Недопустимый язык';
                setErrorCookie('languages', $errors['languages']);
                break;
            }
        }
        setFormCookie('languages', implode(',', $_POST['languages']));
    }

    if (empty($_POST['bio'])) {
        $errors['bio'] = 'Заполните биографию';
        setErrorCookie('bio', $errors['bio']);
    } elseif (strlen($_POST['bio']) > 5000) {
        $errors['bio'] = 'Не более 5000 символов';
        setErrorCookie('bio', $errors['bio']);
    }
    setFormCookie('bio', $_POST['bio']);

    if (empty($_POST['contract'])) {
        $errors['contract'] = 'Необходимо согласие';
        setErrorCookie('contract', $errors['contract']);
    } else {
        setFormCookie('contract', '1');
    }

    if (!empty($errors)) {
        header('Location: index.php');
        exit();
    }

    $user = 'u82392';
    $pass = '1685352';
    $dbname = 'u82392';
    
    try {
        $db = new PDO("mysql:host=localhost;dbname=$dbname", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $db->beginTransaction();

        if (isset($_SESSION['user_id'])) {
            $stmt = $db->prepare("UPDATE applications 
                                  SET fio = ?, phone = ?, email = ?, birthdate = ?, 
                                      gender = ?, bio = ?, contract_agreed = ?
                                  WHERE id = ?");
            $stmt->execute([
                $_POST['fio'],
                $_POST['phone'],
                $_POST['email'],
                $_POST['birthdate'],
                $_POST['gender'],
                $_POST['bio'],
                isset($_POST['contract']) ? 1 : 0,
                $_SESSION['user_id']
            ]);

            $stmt = $db->prepare("DELETE FROM application_languages WHERE application_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            
            $applicationId = $_SESSION['user_id'];
        } else {

            $stmt = $db->prepare("INSERT INTO applications (fio, phone, email, birthdate, gender, bio, contract_agreed)
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['fio'],
                $_POST['phone'],
                $_POST['email'],
                $_POST['birthdate'],
                $_POST['gender'],
                $_POST['bio'],
                isset($_POST['contract']) ? 1 : 0
            ]);
            $applicationId = $db->lastInsertId();

            $login = generateLogin($_POST['fio']);
            $password = generatePassword();
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $db->prepare("INSERT INTO users (application_id, login, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$applicationId, $login, $passwordHash]);

            $_SESSION['new_login'] = $login;
            $_SESSION['new_password'] = $password;
        }

        $stmt = $db->prepare("INSERT INTO application_languages (application_id, language_id)
                              SELECT ?, id FROM programming_languages WHERE name = ?");
        foreach ($_POST['languages'] as $lang) {
            $stmt->execute([$applicationId, $lang]);
        }
        
        $db->commit();

        foreach ($_COOKIE as $name => $value) {
            if (strpos($name, 'form_') === 0 || strpos($name, 'error_') === 0) {
                setcookie($name, '', time() - 3600, '/');
            }
        }

        setErrorCookie('auth', '');
        
        header('Location: index.php?success=1&id='.$applicationId);
        exit();
    } catch (PDOException $e) {
        if (isset($db)) {
            $db->rollBack();
        }
        setErrorCookie('db', 'Ошибка сохранения: '.$e->getMessage());
        header('Location: index.php');
        exit();
    }
}


$userData = [];
if (isset($_SESSION['user_id'])) {
    try {
        $db = new PDO("mysql:host=localhost;dbname=u82392", 'u82392', '1685352', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $stmt = $db->prepare("SELECT * FROM applications WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData) {
            $stmt = $db->prepare("SELECT pl.name FROM application_languages al 
                                  JOIN programming_languages pl ON al.language_id = pl.id 
                                  WHERE al.application_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $languages = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $userData['languages'] = implode(',', $languages);
        }
    } catch (PDOException $e) {
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма анкеты</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-group">
        <h1>Анкета</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success">
                Спасибо, результаты сохранены. ID: <?= htmlspecialchars($_GET['id']) ?>
                <?php if (isset($_SESSION['new_login'])): ?>
                    <br><strong>Ваши учетные данные для входа:</strong><br>
                    Логин: <?= htmlspecialchars($_SESSION['new_login']) ?><br>
                    Пароль: <?= htmlspecialchars($_SESSION['new_password']) ?><br>
                    <span style="font-size: 12px;">Сохраните их! Они понадобятся для редактирования.</span>
                    <?php 
                    unset($_SESSION['new_login']);
                    unset($_SESSION['new_password']);
                    ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_COOKIE['error_db'])): ?>
            <div class="error"><?= htmlspecialchars($_COOKIE['error_db']) ?></div>
        <?php endif; ?>
        
        <?php if (isset($_COOKIE['error_auth'])): ?>
            <div class="error"><?= htmlspecialchars($_COOKIE['error_auth']) ?></div>
        <?php endif; ?>
        
        <?php if (!isset($_SESSION['user_id'])): ?>
            <!-- Форма входа -->
            <div style="background: #f0f0f0; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                <h3>Вход для редактирования</h3>
                <form action="index.php" method="POST">
                    <input type="hidden" name="login_action" value="1">
                    <label>Логин:</label>
                    <input type="text" name="login" required>
                    <label>Пароль:</label>
                    <input type="password" name="password" required>
                    <button type="submit" style="margin-top: 10px;">Войти</button>
                </form>
            </div>
        <?php else: ?>
            <!-- Выход -->
            <div style="background: #d4edda; padding: 10px; margin-bottom: 20px; border-radius: 5px;">
                Вы вошли как: <strong><?= htmlspecialchars($_SESSION['user_login']) ?></strong>
                <a href="?logout=1" style="margin-left: 15px;">Выйти</a>
            </div>
        <?php endif; ?>
        
        <form action="index.php" method="POST">
            <label for="fio">ФИО:</label>
            <input type="text" id="fio" name="fio" 
                   value="<?= htmlspecialchars($userData['fio'] ?? getFormData('fio')) ?>"
                   class="<?= isset($_COOKIE['error_fio']) ? 'error-field' : '' ?>">
            <?php if (isset($_COOKIE['error_fio'])): ?>
                <div class="error"><?= htmlspecialchars($_COOKIE['error_fio']) ?></div>
            <?php endif; ?>

            <label for="phone">Телефон:</label>
            <input type="tel" id="phone" name="phone" 
                   value="<?= htmlspecialchars($userData['phone'] ?? getFormData('phone')) ?>"
                   class="<?= isset($_COOKIE['error_phone']) ? 'error-field' : '' ?>">
            <?php if (isset($_COOKIE['error_phone'])): ?>
                <div class="error"><?= htmlspecialchars($_COOKIE['error_phone']) ?></div>
            <?php endif; ?>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" 
                   value="<?= htmlspecialchars($userData['email'] ?? getFormData('email')) ?>"
                   class="<?= isset($_COOKIE['error_email']) ? 'error-field' : '' ?>">
            <?php if (isset($_COOKIE['error_email'])): ?>
                <div class="error"><?= htmlspecialchars($_COOKIE['error_email']) ?></div>
            <?php endif; ?>

            <label for="birthdate">Дата рождения:</label>
            <input type="date" id="birthdate" name="birthdate" 
                   value="<?= htmlspecialchars($userData['birthdate'] ?? getFormData('birthdate')) ?>"
                   class="<?= isset($_COOKIE['error_birthdate']) ? 'error-field' : '' ?>">
            <?php if (isset($_COOKIE['error_birthdate'])): ?>
                <div class="error"><?= htmlspecialchars($_COOKIE['error_birthdate']) ?></div>
            <?php endif; ?>

            <label>Пол:</label>
            <div class="radio-group">
                <input type="radio" id="male" name="gender" value="male"
                       <?= ($userData['gender'] ?? getFormData('gender')) == 'male' ? 'checked' : '' ?>
                       class="<?= isset($_COOKIE['error_gender']) ? 'error-field' : '' ?>">
                <label for="male">Мужской</label>
            </div>
            <div class="radio-group">
                <input type="radio" id="female" name="gender" value="female"
                       <?= ($userData['gender'] ?? getFormData('gender')) == 'female' ? 'checked' : '' ?>
                       class="<?= isset($_COOKIE['error_gender']) ? 'error-field' : '' ?>">
                <label for="female">Женский</label>
            </div>
            <?php if (isset($_COOKIE['error_gender'])): ?>
                <div class="error"><?= htmlspecialchars($_COOKIE['error_gender']) ?></div>
            <?php endif; ?>

            <label for="languages">Любимый язык программирования:</label>
            <select id="languages" name="languages[]" multiple="multiple"
                    class="<?= isset($_COOKIE['error_languages']) ? 'error-field' : '' ?>">
                <?php
                $savedLanguages = $userData['languages'] ?? getFormData('languages');
                $selectedLangs = $savedLanguages ? explode(',', $savedLanguages) : [];
                $options = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskel', 'Clojure', 'Prolog', 'Scala', 'Go'];
                foreach ($options as $lang): ?>
                    <option value="<?= $lang ?>"
                            <?= in_array($lang, $selectedLangs) ? 'selected' : '' ?>>
                        <?= $lang ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($_COOKIE['error_languages'])): ?>
                <div class="error"><?= htmlspecialchars($_COOKIE['error_languages']) ?></div>
            <?php endif; ?>

            <label for="bio">Биография:</label>
            <textarea id="bio" name="bio"
                      class="<?= isset($_COOKIE['error_bio']) ? 'error-field' : '' ?>"><?= htmlspecialchars($userData['bio'] ?? getFormData('bio')) ?></textarea>
            <?php if (isset($_COOKIE['error_bio'])): ?>
                <div class="error"><?= htmlspecialchars($_COOKIE['error_bio']) ?></div>
            <?php endif; ?>

            <div class="checkbox-group">
                <input type="checkbox" id="contract" name="contract" value="1"
                       <?= (isset($userData['contract_agreed']) && $userData['contract_agreed']) || getFormData('contract') ? 'checked' : '' ?>
                       class="<?= isset($_COOKIE['error_contract']) ? 'error-field' : '' ?>">
                <label for="contract">С контрактом ознакомлен(а)</label>
            </div>
            <?php if (isset($_COOKIE['error_contract'])): ?>
                <div class="error"><?= htmlspecialchars($_COOKIE['error_contract']) ?></div>
            <?php endif; ?>

            <button type="submit">Сохранить</button>
        </form>
    </div>
</body>
</html>
