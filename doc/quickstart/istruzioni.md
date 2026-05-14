# Quickstart — Installazione dal backup JPA

Questa cartella contiene un backup completo di un sito Joomla di esempio preconfigurato con il Template Accessibile (`quickstart-JoomlaPA-Akeeba.jpa`). Può essere installato su qualsiasi server PHP per ottenere un sito funzionante già configurato, senza dover installare Joomla da zero.

Il ripristino avviene tramite **Akeeba Kickstart**, uno strumento PHP gratuito che estrae l'archivio `.jpa` direttamente sul server e avvia lo script di ripristino.

---

> [!CAUTION]
> ## 🔐 IMPORTANTE — IMPOSTA LA PASSWORD DEL SUPER ADMIN DURANTE L'INSTALLAZIONE
>
> Il pacchetto include un utente Super Admin preconfigurato:
>
> - **Username:** `joomlapa_admin`
>
> Durante lo script di ripristino Akeeba, nella sezione **"Super Administrator settings"**, **NON lasciare vuoto il campo password**: inserisci una **nuova password a tua scelta**, che diventerà la password effettiva di `joomlapa_admin` sul sito ripristinato.
>
> ❗ Se lasci il campo vuoto verrà mantenuta la password originale del pacchetto, che **tu non conosci** — risultato: non riuscirai più ad accedere al backend del sito appena installato.
>
> Scegli una password robusta (almeno 12 caratteri, con maiuscole, minuscole, numeri e simboli) e annotala in un posto sicuro.

---

## Requisiti

- PHP 8.2 o superiore
- MySQL 8.0+ o MariaDB 10.4+
- Spazio disco: almeno 50 MB liberi
- Un database vuoto già creato, con utente e password

---

## 1. Scaricare Kickstart

Scarica l'ultima versione gratuita di Kickstart (Core) da:

**<https://www.akeeba.com/download.html#kickstart>**

Il file scaricato è uno ZIP. Estrailo localmente: otterrai il file `kickstart.php`.

> **Nota sulla sicurezza:** Prima di caricarlo sul server, rinomina `kickstart.php` con un nome che non contenga le parole `kickstart`, `ks` o `akeeba` (es. `ripristino.php`), mantenendo l'estensione `.php`. Questo riduce il rischio che venga trovato da scanner automatici.

---

## 2. Caricare i file sul server

Carica nella **cartella radice del sito** (solitamente `public_html`, `httpdocs`, `www` o il nome del dominio) i seguenti file:

- `kickstart.php` (o il nome scelto)
- `quickstart-JoomlaPA-Akeeba.jpa`

> **Importante:** Se usi FTP, imposta la modalità di trasferimento su **Binary** (non Auto né ASCII), sia per `kickstart.php` sia per il `.jpa`. Il trasferimento in modalità testo corrompe entrambi i file.

---

## 3. Avviare Kickstart

Apri nel browser l'URL del file Kickstart:

```
https://tuodominio.it/kickstart.php
```

Alla prima apertura viene mostrato un **dialogo iniziale** con avvisi di sicurezza. Leggi e premi **ESC** (o il pulsante di conferma) per proseguire alla pagina di configurazione.

---

## 4. Pagina di configurazione (Setup)

La pagina è divisa in quattro sezioni.

### Sezione 1 — Selezione dell'archivio

Kickstart cerca automaticamente i file `.jpa` e `.zip` nella sua stessa directory. Seleziona `quickstart-JoomlaPA-Akeeba.jpa` dal menu a tendina.

### Sezione 2 — Metodo di estrazione

Scegli il metodo più adatto al tuo server:

| Metodo | Quando usarlo |
|---|---|
| **Directly** | Server con suPHP o nuova installazione — scrittura diretta dei file |
| **FTP** | Se la scrittura diretta fallisce per problemi di permessi — i file passano per una directory temporanea poi vengono caricati via FTP |
| **Hybrid** *(consigliato)* | Tenta prima la scrittura diretta; in caso di errore di permessi passa automaticamente a FTP |

Se scegli **FTP** o **Hybrid**, compila i campi che appaiono:

- **FTP Host Name** — indirizzo del server FTP (es. `ftp.tuodominio.it`, non l'URL)
- **FTP Port** — di norma `21`
- **FTPS** — abilita solo se il tuo hosting richiede FTP over SSL
- **Passive Mode** — lascia attivo (è la modalità predefinita consigliata)
- **FTP User / Password** — credenziali FTP
- **FTP Directory** — percorso FTP assoluto della cartella radice (es. `/public_html`)
- **Temporary Directory** — directory temporanea scrivibile (Kickstart ne suggerisce una automaticamente)

### Sezione 3 — Opzioni avanzate

Le impostazioni predefinite vanno bene per la maggior parte dei casi. Verifica solo:

- **Rename Server Configuration Files** — deve essere **attivo**: rinomina `.htaccess`, `web.config` e simili in `.bak` durante l'estrazione per evitare conflitti, poi li ripristina al termine.
- **Maximum Execution Time** — abbassalo se il server ha un `max_execution_time` basso (es. 30 secondi); Kickstart si auto-interrompe e riprende automaticamente.

### Sezione 4 — Avvio

Clicca il **pulsante verde** per avviare l'estrazione. La barra di avanzamento mostra lo stato. L'operazione può richiedere qualche minuto a seconda delle dimensioni dell'archivio e della velocità del server.

---

## 5. Script di ripristino (Akeeba Restoration Script)

Al termine dell'estrazione, Kickstart mostra un pulsante per **lanciare l'installer**. Cliccalo: si aprirà lo script di ripristino Akeeba (`installation/index.php`) in una nuova finestra o tab.

> **Non chiudere la finestra di Kickstart** fino al completamento del ripristino.

Lo script di ripristino guida nei seguenti passi:

1. **Controllo prerequisiti** — verifica PHP, database e permessi.
2. **Configurazione database** — inserisci i dati del database creato in precedenza:
   - Host (di solito `localhost`)
   - Nome database
   - Utente database
   - Password
   - Prefisso tabelle (lascia quello suggerito oppure cambiane uno personalizzato)
3. **Configurazione sito** — URL e percorso del sito vengono rilevati automaticamente; verifica che siano corretti.
4. 🔐 **Super Administrator settings — PASSAGGIO CRITICO**

   In questa sezione lo script mostra i dati dell'utente Super Admin del pacchetto. **Devi impostare qui la password con cui accederai al backend dopo il ripristino**:

   - **Username:** lascia `joomlapa_admin` (o cambialo se preferisci un altro nome)
   - **Email:** sostituisci l'email del pacchetto con la tua
   - **Password / Conferma password:** ⚠️ **NON lasciare vuoti questi campi.** Inserisci una **nuova password a tua scelta** — sarà la password effettiva dell'utente `joomlapa_admin` sul sito ripristinato.

   > ⚠️ Se lasci il campo password vuoto, verrà mantenuta la password originale del pacchetto, che non conosci: non potrai più accedere al backend e dovrai resettarla manualmente da CLI o database.

5. **Fine** — lo script completa il ripristino e mostra il riepilogo. Chiudi questa finestra.

---

## 6. Pulizia finale

Torna alla finestra di Kickstart e clicca **Clean Up**. Kickstart rimuoverà automaticamente:

- La cartella `installation/`
- L'archivio `.jpa`
- Il file `kickstart.php` (o il nome scelto)
- I file di traduzione di Kickstart
- I file di configurazione server rinominati in `.bak` (ripristinati ai nomi originali)

Al termine puoi aprire il sito e accedere al pannello di amministrazione Joomla con `joomlapa_admin` e la password che hai impostato al passo 5.

---

## Credenziali di accesso al backend dopo il ripristino

| Campo | Valore |
|---|---|
| **Username Super Admin** | `joomlapa_admin` (modificabile durante il ripristino) |
| **Password Super Admin** | 🔐 **Quella che imposti tu** nel passo 5 dello script di ripristino. Non lasciare il campo vuoto, altrimenti la password resta quella originale del pacchetto (non nota a te). |

> **Hai lasciato il campo password vuoto per errore?**
> Puoi resettare la password del super utente da CLI senza ricominciare l'installazione:
> ```bash
> php cli/joomla.php user:reset-password --username=joomlapa_admin
> ```

---

## Problemi comuni

| Problema | Soluzione |
|---|---|
| Nessun archivio elencato nella sezione 1 | Verifica che il `.jpa` sia nella stessa cartella di `kickstart.php` e che il trasferimento FTP sia avvenuto in modalità Binary |
| Errore 403 all'apertura di Kickstart | Il file `.htaccess` esistente blocca l'accesso: rinominalo temporaneamente in `.htaccess.bak` |
| Estrazione bloccata / AJAX error | Abbassa il valore di **Maximum Execution Time** nella sezione 3 |
| Errore di connessione al database | Verifica host, nome database, utente e password; su alcuni hosting l'host non è `localhost` ma un indirizzo specifico |
| `Parse Error` all'apertura di Kickstart | Il file è stato trasferito in modalità ASCII anziché Binary: ricaricalo in modalità Binary |
| Non riesco ad accedere al backend perché ho lasciato vuoto il campo password al passo 5 | Resetta la password via CLI: `php cli/joomla.php user:reset-password --username=joomlapa_admin` |
