\# Testimi i API-së – Sistemi Porosive Online



Testet janë realizuar me Postman në adresën:

http://localhost/porosi/backend/api



\## Testet e autentikimit dhe autorizimit



| Testi | Rezultati |

|---|---|

| Login me kredenciale të sakta | PASS – 200 OK |

| Login me kredenciale të pasakta | PASS – 401 INVALID\_CREDENTIALS |

| Klienti tenton të krijojë produkt | PASS – 403 FORBIDDEN |



\## Testet e produkteve



| Testi | Rezultati |

|---|---|

| GET lista e produkteve | PASS – 200 OK |

| GET produkt që nuk ekziston | PASS – 404 PRODUCT\_NOT\_FOUND |

| POST krijimi i produktit | PASS – 201 Created |

| PATCH përditësimi i produktit | PASS – 200 OK |

| DELETE produkti i krijuar | PASS – produkti u fshi |

| DELETE produkt i përdorur në porosi | PASS – 409 PRODUCT\_IN\_USE |



\## Testet e porosive



| Testi | Rezultati |

|---|---|

| GET porositë e klientit | PASS – 200 OK |

| POST krijimi i porosisë | PASS – 201 Created |

| Kontrolli i stokut pas porosisë | PASS – stoku u zvogëlua |

| POST porosi me shportë bosh | PASS – 422 CART\_EMPTY |

| PATCH ndryshimi i statusit nga admin | PASS – 200 OK |



\## Përfundim



Testet treguan se API-ja realizon operacionet kryesore CRUD,

autentikimin, autorizimin dhe menaxhimin e porosive.

Gjithashtu, API-ja kthen gabime të përshtatshme kur kërkesat

janë të pavlefshme ose kur veprimi nuk lejohet.

