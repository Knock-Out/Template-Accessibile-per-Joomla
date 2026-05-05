# Template Accessibile per Joomla 5+ (Modello Universale / Bootstrap Italia)

> ## ⚠️ IL TEMPLATE NON È PRONTO PER SITI DI PRODUZIONE
>
> Questo è un progetto **WORK IN PROGRESS**. Al momento il template **non supera ancora** i controlli dei validatori del Dipartimento per la Trasformazione Digitale — vedi [App di valutazione per i siti di Comuni e Scuole, pubblicata la versione 2.0](https://innovazione.gov.it/notizie/articoli/app-di-valutazione-per-i-siti-di-comuni-e-scuole-pubblicata-la-versione-2-0/).
>
> Chiunque può **scaricarlo, testarlo, provarlo e contribuire**: segnalazioni, PR e feedback sono benvenuti. Non utilizzarlo però come base di un sito istituzionale reale finché la conformità ai validatori ufficiali non sarà completa.

---

## 📚 Documentazione completa

> **[→ Vai all'indice della documentazione](doc/it/indice.md)**
>
> La documentazione contiene tutto il necessario per **installare, configurare e gestire** il template: guida passo-passo all'installazione, configurazione del backend, struttura degli articoli, layout e override inclusi, conformità al Modello Comuni.

---

## 📄 Descrizione

Questo è il template pensato per integrare il **Modello Universale** e il design system di **Designers Italia** nativamente su **Joomla 5+**. Il template nasce per la **Pubblica Amministrazione italiana** (Comuni, Scuole, ASL, Musei), ma è adatto a qualsiasi sito che voglia seguire gli stessi standard di accessibilità.

Progettato senza framework pesanti (zero jQuery, CSS vanilla e JavaScript nativo), punta a garantire un'esperienza **altamente accessibile (WCAG 2.1 AA/AAA)**, performante e a prova di aggiornamenti. La struttura si basa su **Bootstrap Italia 2.9.0** e sfrutta i *Web Asset Manager* e i namespace nativi di Joomla 5.

## 🛠 Requisiti di sistema

- **Joomla!**: 5.0.0 o superiore
- **PHP**: 8.2.0 o superiore

## 🚀 Installazione

L'installazione segue la classica procedura standard di Joomla. Nessuna riga di codice richiesta.

1. Vai nella pagina [Releases](https://github.com/PixedBo/Template-Accessibile-per-Joomla/releases) di questo repository.
2. Scarica l'ultima versione del pacchetto di installazione (es. `tpl_accessibile_vX.X.X.zip`).
3. Accedi al backend del tuo sito Joomla.
4. Naviga in **Sistema** > **Installa** > **Estensioni**.
5. Trascina il file `.zip` scaricato nell'area di caricamento.
6. Vai su **Sistema** > **Stili Template (Sito)** e imposta "Template Accessibile" come predefinito (cliccando sulla stellina).

---

## ⚙️ Configurazione

Il template è "chiavi in mano": tutto si configura dal pannello **Stili Template → Template Accessibile** nel backend di Joomla, senza toccare il codice.

Le opzioni principali includono logo, nome ente, colore primario (5 temi cromatici accessibili), header, social network, footer e blocco "Contatta".

**→ Guida completa:** [`doc/it/configurazione-joomla.md`](doc/it/configurazione-joomla.md)  
**→ Colori e temi:** [`doc/it/colori-e-temi.md`](doc/it/colori-e-temi.md)

---

## 📐 Posizioni Modulo

Il template dichiara le seguenti posizioni native:

`selezione-lingua` · `menu-principale` · `menu-secondario` · `percorso` · `top` · `top-muted` · `evidenza` · `calendario` · `colonna-sinistra` · `colonna-destra` · `bottom` · `bottom2` · `footer1` · `footer2`

I moduli menu devono usare il layout alternativo `comuni-menu`.

**→ Guida completa con descrizione di ogni posizione:** [`doc/it/moduli.md`](doc/it/moduli.md)

---

## 💻 CSS Personalizzato

Per aggiungere stili CSS personalizzati senza modificare i file originali, crea il file `/templates/tpl_accessibile/css/custom.css`. Il sistema lo rileva automaticamente e lo carica per ultimo.

---

## 🌟 Override e Layout Alternativi inclusi

Il template include override per i principali componenti Joomla (articoli, menu, breadcrumb, categorie) e layout alternativi specializzati per il Modello Comuni:

| Layout | Descrizione |
|--------|-------------|
| `com_content/article/default` | Articolo standard con tempo di lettura, scrollspy, social sharing |
| `com_content/article/note-legali` | Pagina Note Legali (C.SI.3.4) con sezione CC-BY 4.0 obbligatoria |
| `com_content/article/scheda-servizio` | Scheda Servizio comunale (C.SI.1.3) con JSON-LD e widget feedback |
| `com_content/category/notizie` | Categoria Notizie con articoli "In evidenza" in cima |
| `com_content/category/servizi` | Categoria Servizi con ricerca e sottocategorie |
| `com_content/category/amministrazione` | Categoria Amministrazione con sezione "In evidenza" |
| `com_content/category/vivere` | Pagina "Vivere il Comune" con eventi e luoghi |
| `mod_articles/evidenza-singolo` | Modulo articoli: singolo o slideshow accessibile automatico |
| `mod_articles/evidenza-tre-colonne` | Modulo articoli: layout a 3 colonne card |
| `mod_menu/comuni-menu` | Layout menu obbligatorio per header Bootstrap Italia |
| `mod_breadcrumbs/default` | Breadcrumb con microdata schema.org e `data-element` |

**→ Guida all'inserimento articoli:** [`doc/it/inserimento-articoli.md`](doc/it/inserimento-articoli.md)  
**→ Layout Notizie:** [`doc/it/layout-notizie.md`](doc/it/layout-notizie.md)  
**→ Layout Servizi:** [`doc/it/layout-servizi.md`](doc/it/layout-servizi.md)  
**→ Layout Amministrazione:** [`doc/it/layout-amministrazione.md`](doc/it/layout-amministrazione.md)  
**→ Layout Vivere il Comune:** [`doc/it/layout-vivere.md`](doc/it/layout-vivere.md)  
**→ Scheda Servizio:** [`doc/it/scheda-servizio.md`](doc/it/scheda-servizio.md)

---

## 🏛️ Conformità Modello Comuni (Designers Italia)

Il template implementa i `data-element` richiesti dall'App Valutazione Modelli:

- Mapping voci di menu → `data-element` funzionali (scheda "Valutazione Comuni")
- Widget "Valutazione chiarezza pagina" (C.SI.2.5/2.6) — struttura HTML completa, risposte non ancora salvate
- Layout Scheda Servizio (C.SI.1.3) con tutti i `data-element` e JSON-LD `GovernmentService`
- Pagina Note Legali (C.SI.3.4) con testo CC-BY 4.0 non modificabile

**→ Guida completa:** [`doc/it/valutazione-modelli.md`](doc/it/valutazione-modelli.md)  
**→ Percorso completo per un sito Comune:** [`doc/it/indice.md`](doc/it/indice.md)

## 🧪 Stato della conformità

- ✅ Menu principale con `data-element="main-navigation"` e login con `data-element="personal-area-login"`
- ✅ Breadcrumb con `data-element="breadcrumb"` e microdata schema.org
- ✅ Layout Servizi con `service-link` + `service-category-link`
- ✅ Pagina Note Legali (C.SI.3.4) con `data-element="legal-notes"` e licenza CC-BY 4.0
- ✅ Layout Scheda Servizio (C.SI.1.3) con tutti i `data-element` e JSON-LD
- ✅ Layout Notizie, Amministrazione, Vivere il Comune
- ⚠️ Widget feedback (C.SI.2.5): struttura HTML completa, ma le risposte **non vengono salvate** — è un placeholder per superare i controlli dell'App Valutazione Modelli
- 🚧 Layout specializzati per Ufficio, Documento, Luogo, Evento, Persona, Notizia (articolo singolo) non ancora implementati

---

## 🤝 Contribuire

Il progetto è aperto ai contributi della community:

- Apri una **issue** per segnalare bug, mancanze di conformità o proporre nuove funzionalità.
- Fai una **pull request** se hai sistemato qualcosa (fix, nuovi override, nuovi layout dedicati).
- Testa il template installandolo su un ambiente di sviluppo e lancia l'[App di valutazione per i siti di Comuni e Scuole](https://innovazione.gov.it/notizie/articoli/app-di-valutazione-per-i-siti-di-comuni-e-scuole-pubblicata-la-versione-2-0/) per verificare quali criteri non passano ancora.

## 📜 Licenza e Crediti

Questo template è rilasciato sotto licenza **GNU GPL v3**.  
Basato sulle risorse UI/UX di [Designers Italia](https://designers.italia.it/) e sul framework [Bootstrap Italia](https://italia.github.io/bootstrap-italia/).
