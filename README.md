# GentaOlloni-SistemiPorosiveOnline

Tema: Sistemi për Menaxhimin e Porosive Online (E-Commerce)

## Raporti 1

Ideja është të bëhet një sistem për menaxhimin e porosive online, në mënyrë që klientët të shohin produktet dhe të bëjnë porosi më lehtë, ndërsa administratori t'i menaxhojë produktet dhe porositë në një vend të vetëm, pa gabime dhe pa porosi të humbura.

## Kërkesat

Kërkesat për këtë sistem janë:

- sistemi duhet të lejojë qasjen e klientëve dhe të administratorit

- klienti mund të regjistrohet dhe të hyjë me email dhe fjalëkalim

- klienti mund t'i shohë produktet dhe t'i kërkojë ose filtrojë sipas kategorisë dhe çmimit

- klienti mund të shohë detajet e produktit (përshkrimi, çmimi dhe sa copë ka në stok)

- klienti mund të shtojë produkte në shportë, t'i heqë ato ose t'ia ndryshojë sasinë

- klienti mund ta bëjë porosinë duke shkruar adresën e dërgesës, ku pagesa bëhet kur e merr porosinë

- klienti mund ta shohë historikun e porosive dhe në çfarë statusi janë

- klienti mund ta anulojë porosinë vetëm derisa ajo nuk është konfirmuar ende nga admini

- administratori mund të shtojë, ndryshojë dhe fshijë produkte dhe kategori

- administratori mund t'i shohë të gjitha porositë dhe ta ndryshojë statusin e tyre (në pritje, konfirmuar, dërguar, dorëzuar ose anuluar)

- kur bëhet një porosi, stoku i produktit zvogëlohet vetë, dhe nëse nuk ka mjaftueshëm produkte porosia nuk pranohet

...

- në website duhet të konfirmohet regjistrimi, pra autentifikim

- fjalëkalimet duhet të ruhen me hash (të fshehura, jo si tekst i lexueshëm)

- çdo përdorues, produkt dhe porosi duhet të ketë ID të veçantë

- website duhet të jetë responsiv për çdo lloj ekrani

- duhet të jetë i lehtë për t'u kuptuar dhe për t'u përdorur

- secili rol (klient ose administrator) duhet të bëjë vetëm funksionet e veta

## Teknikat/arkitektura

frontend

HTML, CSS, JavaScript, Bootstrap

faqet e klientit dhe të administratorit, dizajn responsiv

backend

PHP (me XAMPP): hyrja dhe regjistrimi, rolet, produktet, shporta dhe porositë

database

MySQL: përdoruesit, produktet, kategoritë, shporta, porositë dhe pagesat

Modeli fillestar i databazës:

| Tabela | Për çfarë shërben |
|---|---|
| users | Llogaritë, email-i, password hash dhe roli (klient ose administrator) |
| addresses | Adresat e dërgesës të klientit |
| categories | Kategoritë e produkteve |
| products | Produktet me çmim, stok dhe kategori |
| cart_items | Produktet që klienti i ka në shportë |
| orders | Porositë me klientin, adresën, statusin dhe totalin |
| order_items | Produktet brenda një porosie, me sasinë dhe çmimin |
| payments | Pagesa e secilës porosi |

Lidhjet kryesore midis tabelave:

- një përdorues mund të ketë shumë porosi
- një kategori ka shumë produkte
- një porosi ka shumë produkte brenda (tabela order_items)
- një porosi ka një pagesë

arkitektura

sistemi ndahet në tri pjesë: frontend (çfarë e shohin përdoruesit), backend (PHP, që i përpunon kërkesat) dhe database (MySQL, ku ruhen të dhënat)

Modulet: përdoruesit, produktet, shporta, porositë, pagesat, administrimi

## Plan pune

sugjerimi: kërkesat -> dizajnimi -> implementimi -> testimi

| Faza | Aktivitetet kryesore | Rezultati i pritshëm |
|---|---|---|
| Analiza e kërkesave | Përcaktimi i funksioneve dhe roleve të sistemit | Lista e kërkesave |
| Dizajnimi | Arkitektura dhe modeli i databazës | Dizajni i sistemit |
| Krijimi i databazës | Krijimi i tabelave dhe lidhjeve në MySQL | Databaza e gatshme |
| Backend-i | Hyrja, regjistrimi, produktet, shporta dhe porositë në PHP | Pjesa e serverit funksionale |
| Frontend-i | Faqet për klientin dhe administratorin | Ndërfaqe responsive |
| Testimi | Provimi i funksioneve dhe rregullimi i gabimeve | Version i testuar |

Orari i propozuar

Java 1: analiza e kërkesave, dizajni dhe databaza
Java 2-3: backend-i (hyrja, produktet, shporta, porositë)
Java 4-5: frontend-i dhe lidhja me backend-in
Java 6-7: testimi dhe rregullimi i gabimeve
Java 8: finalizimi
