# TYPO3 extension "mailtonews"#

This extension makes it possible to import emails as news entry which is just awesome for editors who are not used to TYPO3.

**Important:** This extension is highly in dev mode, only tested with a hot 6.2 and not on many environments. Please be extra careful if using on production sites!!

## Requirements ##

- TYPO3 CMS 6.2
- smtp support in PHP, especially in CLI environment.


## Configuration ##

Add a configuration mode as below

```
gmail {
	username =
	password =
	host = {imap.gmail.com:993/imap/ssl}INBOX

	configuration {
		importClass = GeorgRinger\Mailtonews\Service\Import\BasicImport

		# see php.net/imap_search 2nd argument
		searchCriteria = UNSEEN

		defaultValues {
			pid = 3
			hidden = 0
		}

		deleteMailAfterImport =

		notifications {
			onFailure = 1
			onSuccess =
			recipient =
		}

		imagesAsContentElement = 1
		imagesAsContentElement {
		}

	}
}
```

## Start import ##

Use CLI (backend user _cli_lowlevel is needed) by calling

```
./typo3/cli_dispatch.phpsh extbase mailtonews:import:run --mode=gmail
```

The mode is the same as the one in the configuration which makes it possible to handle multiple imports.