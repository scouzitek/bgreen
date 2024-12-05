<?php
// Inclure le fichier autoload de Composer pour PHPMailer
require '../vendor/autoload.php';

// Utilisation des classes PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Paramètres de connexion à la base de données MySQL
$servername = "localhost"; // Par défaut pour MAMP
$username = "root"; // Nom d'utilisateur MySQL par défaut dans MAMP
$password = "root"; // Mot de passe MySQL par défaut dans MAMP
$dbname = "zerowaste_contact"; // Nom de votre base de données

// Créer la connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("La connexion a échoué : " . $conn->connect_error);
}

// Vérification que les champs du formulaire sont envoyés et récupérer les données
$name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
$email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
$message = isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '';

// Vérification que les champs obligatoires sont remplis
if (empty($name) || empty($email) || empty($message)) {
    die('Tous les champs obligatoires doivent être remplis.');
}

// Préparer la requête SQL pour insérer les données dans la table
$sql = "INSERT INTO formulaire_contact (name, email, message)
        VALUES (?, ?, ?)";

// Préparer la requête
$stmt = $conn->prepare($sql);

// Lier les paramètres à la requête (sécurisation contre les injections SQL)
$stmt->bind_param("sss", $name, $email, $message);

// Exécuter la requête et vérifier si l'insertion a réussi
if ($stmt->execute()) {
    // --- Envoi de l'email avec PHPMailer ---
    $mail = new PHPMailer(true); // Créer une instance de PHPMailer

    try {
        // Paramétrage de l'envoi via SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Le serveur SMTP de Gmail
        $mail->SMTPAuth = true;
        $mail->Username = 'thomas.gvlns@gmail.com'; // Ton adresse email Gmail
        $mail->Password = 'tnfs toyk ysfp kulx'; // Ton mot de passe ou mot de passe d'application
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8'; // Définir le charset sur UTF-8

        // Destinataire
        $mail->setFrom($email, $name);
        $mail->addAddress('rajakoski.aleksi@gmail.com'); // Ton adresse email

        // Contenu du message
        $mail->isHTML(true);
        $mail->Subject = 'Message de contact';
        $mail->Body = "
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Message de contact depuis le site</title>
            </head>
            <body>
                <h2>Vous avez reçu un nouveau message de contact</h2>
                <p><strong>Nom :</strong> $name</p>
                <p><strong>Email :</strong> $email</p>
                <p><strong>Message :</strong> $message</p>
            </body>
            </html>
        "; // Le contenu HTML du message

        // Envoi de l'email
        if ($mail->send()) {
            echo 'Votre message a été envoyé et enregistré avec succès !';
        } else {
            echo 'Une erreur s\'est produite lors de l\'envoi de l\'email.';
        }

    } catch (Exception $e) {
        echo "Le message n'a pas pu être envoyé. Erreur : {$mail->ErrorInfo}";
    }
} else {
    echo "Une erreur s'est produite lors de l'envoi de votre message. Veuillez réessayer.";
}

// Fermer la connexion et la requête préparée
$stmt->close();
$conn->close();
?>
