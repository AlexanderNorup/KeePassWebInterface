# KeePassWebInterface
#####Access KeePass 2.x files stored on a webserver via your browser. 

This project was made in order to be able to access your KeePass passwords from a remote website. 
It is only a password reader, and not a writer. You **cannot** edit/delete entries via the web using this project.

KeePass is a free open source password manager. A really great free alternative to the expensive alternatives. KeePass can be downloaded from here: https://keepass.info/

After the database is first unlocked through the application, the application will save an "index" of the database conatining only entry titles and usernames. Passwords and other data is ALWAYS encrypted and never stored in plain-text. They're only decrypted when requested using the master password. 

##Features:
- Online KeePass database decrypter.
- Viewing passwords in a KeePass file stored on a webserver remotely.
- Viewing other information from your entries like 'Urls', 'Notes' and other attributes you may add through the KeePass client.
- Use a QR-code to enter your master password.

##Requirements:
- You use a KeePass 2.x database
- You have a **secured** webserver. This page will refuse to run when not accessed through HTTPS.
- Your KeePass .kdbx file is stored somewhere on your server. 
- You have composer installed. Download heree: https://getcomposer.org/

##How to install:
1. Download the latest release from https://github.com/AlexanderNorup/KeePassWebInterface/releases (Download the Source Code from the release)
2. Extract the zip file somewhere on your computer.
3. Navigate into the unzipped folder from the commandline and run: `composer install`
4. Open the settings.php file, and edit the `$kdbxPath` variable so it points to your kdbx file relatively from the settings.php file.
5. The application should now be fully configured, and ready to be accessed from the web.

#Important:
Even though your kdbx file is encrypted, make sure you place it in a folder that's NOT accessible from the web so people can't just download it of your server.
Likewise, make sure that your webpage is only accessible by trusted users. Even though the application keeps the kdbx file decrypted, it will still store usernames, titles and icons in plain text to save on computational resources. 

Putting your KeePass files avaiable on the internet, with or without this application, is at your own risk and security precautions. I, the developer, can not be held accountable for any loss or security breach you might have. 