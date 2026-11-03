\# Dokumentimi i databazës



Emri i databazës: porosi\_online



Databaza përmban 8 tabela:

users, addresses, categories, products, cart\_items,

orders, order\_items dhe payments.



\## Verifikimi



Gjatë kontrollit në phpMyAdmin u konfirmuan:

\- 8 tabela me motorin InnoDB.

\- 9 lidhje Foreign Key.

\- Çelësat primarë dhe lidhjet ndërmjet tabelave.



\## Lidhjet Foreign Key



| Tabela | Kolona | Tabela referente |

|---|---|---|

| addresses | user\_id | users.id |

| cart\_items | product\_id | products.id |

| cart\_items | user\_id | users.id |

| orders | address\_id | addresses.id |

| orders | user\_id | users.id |

| order\_items | product\_id | products.id |

| order\_items | order\_id | orders.id |

| payments | order\_id | orders.id |

| products | category\_id | categories.id |



\## Përfundim



Struktura e databazës dhe lidhjet ndërmjet tabelave

janë verifikuar me sukses përmes phpMyAdmin.

