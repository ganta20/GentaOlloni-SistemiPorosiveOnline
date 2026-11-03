\# Sistemi për Menaxhimin e Porosive Online



\*\*Projekti:\*\* GentaOlloni-SistemiPorosiveOnline  

\*\*Lënda:\*\* Praktikë Profesionale  

\*\*Tema:\*\* Sistemi për Menaxhimin e Porosive Online (E-Commerce)



\## 1. Përshkrimi i projektit



Ky projekt synon zhvillimin e një sistemi për menaxhimin e porosive online. Sistemi mundëson shfaqjen dhe menaxhimin e produkteve, krijimin e porosive dhe administrimin e tyre.



Sistemi është i orientuar në dy role kryesore:

\- \*\*Klienti:\*\* mund të kyçet, të shikojë produktet dhe të realizojë porosi.

\- \*\*Administratori:\*\* mund të menaxhojë produktet dhe porositë.



\## 2. Teknologjitë e përdorura



\- PHP – logjika e backend-it dhe API.

\- MySQL – ruajtja e të dhënave.

\- XAMPP – serveri lokal Apache dhe MySQL.

\- PDO – komunikimi me databazën.

\- Postman – testimi i endpoint-eve të API-së.

\- Git dhe GitHub – menaxhimi i versioneve të projektit.



\## 3. Struktura e projektit



```text

porosi/

├── backend/

│   ├── api/

│   │   ├── auth.php

│   │   ├── index.php

│   │   ├── orders.php

│   │   ├── products.php

│   │   └── .htaccess

│   ├── config/

│   │   └── db.php

│   ├── helpers/

│   │   ├── auth.php

│   │   └── response.php

│   └── database.sql

├── docs/

│   ├── arkitektura.png

│   ├── ERdiagrami.drawio.png

│   └── testimi-api.md

├── frontend/

├── README.md

└── Raporti\_Teknik\_1.docx

```



\## 4. Databaza



Emri i databazës është `porosi\_online`.



Databaza përmban tabelat kryesore:

\- `users` – përdoruesit dhe rolet.

\- `addresses` – adresat e dërgesës.

\- `categories` – kategoritë e produkteve.

\- `products` – produktet, çmimet dhe stoku.

\- `cart\_items` – artikujt në shportë.

\- `orders` – porositë.

\- `order\_items` – produktet brenda porosive.

\- `payments` – informacionet e pagesave.



Lidhja me databazën realizohet përmes PDO në `backend/config/db.php`.



\## 5. Funksionalitetet e backend-it



Backend-i përmban API për:



\- Autentikimin dhe menaxhimin e sesionit.

\- Shfaqjen e produkteve.

\- Krijimin, përditësimin dhe fshirjen e produkteve.

\- Kontrollin e autorizimit sipas rolit.

\- Shfaqjen dhe krijimin e porosive.

\- Përditësimin e statusit të porosive.

\- Kontrollin e stokut gjatë krijimit të porosisë.



\## 6. Endpoint-et e API-së



Adresa bazë:



`http://localhost/porosi/backend/api`



| Metoda | Endpoint | Përshkrimi |

|---|---|---|

| POST | `/auth/login` | Kyçja e përdoruesit |

| POST | `/auth/logout` | Dalja nga sistemi |

| GET | `/products` | Lista e produkteve |

| GET | `/products/{id}` | Detajet e produktit |

| POST | `/products` | Krijimi i produktit |

| PATCH | `/products/{id}` | Përditësimi i produktit |

| DELETE | `/products/{id}` | Fshirja e produktit |

| GET | `/orders` | Lista e porosive |

| POST | `/orders` | Krijimi i porosisë |

| PATCH | `/orders/{id}` | Përditësimi i statusit të porosisë |



\## 7. Instalimi dhe ekzekutimi



1\. Instalo XAMPP.

2\. Kopjo projektin në folderin `C:\\xampp\\htdocs\\porosi`.

3\. Hap XAMPP Control Panel.

4\. Aktivizo Apache dhe MySQL.

5\. Hape phpMyAdmin në `http://localhost/phpmyadmin`.

6\. Importo databazën nga `backend/database.sql`.

7\. Kontrollo konfigurimin në `backend/config/db.php`.

8\. Hape API-në në:



`http://localhost/porosi/backend/api/products`



\*\*Kujdes:\*\* Skedari `database.sql` e fshin dhe e krijon përsëri databazën `porosi\_online`. Importimi mund t'i fshijë të dhënat ekzistuese. Përdore vetëm kur dëshiron instalim të ri ose pasi të kesh bërë kopje rezervë.



\## 8. Testimi



API është testuar përmes Postman.



Testet përfshijnë:

\- Kyçjen me kredenciale të sakta dhe të pasakta.

\- Kontrollin e autorizimit për klient dhe administrator.

\- Operacionet CRUD për produkte.

\- Krijimin dhe menaxhimin e porosive.

\- Kontrollin e stokut.

\- Trajtimin e gabimeve dhe kërkesave të pavlefshme.



Rezultatet e testeve gjenden në `docs/testimi-api.md`.



\## 9. Dokumentimi



Në folderin `docs` gjenden:

\- `arkitektura.png` – diagrami i arkitekturës.

\- `ERdiagrami.drawio.png` – diagrami i databazës.

\- `testimi-api.md` – rezultatet e testimit të API-së.



\## 10. Gjendja e projektit



Backend-i dhe API-ja për funksionalitetet e testuara janë implementuar dhe testuar. Frontend-i mbetet për t'u zhvilluar ose plotësuar në fazën përkatëse të projektit.





