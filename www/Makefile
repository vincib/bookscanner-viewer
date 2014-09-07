all: locales/fr_FR/LC_MESSAGES/messages.mo
# locales/es_ES/LC_MESSAGES/messages.mo locales/de_DE/LC_MESSAGES/messages.mo locales/it_IT/LC_MESSAGES/messages.mo

messages.pot: *.php
	[ -r $@ ] || touch $@
	xgettext --package-name=BookscannerManager --package-version=2014.1 --force-po -o $@ --keyword=__ --keyword=_  --from-code=UTF-8 $^

locales/%/LC_MESSAGES/messages.po: messages.pot
	msgmerge -v -U $@ $^

locales/fr_FR/LC_MESSAGES/messages.mo: locales/fr_FR/LC_MESSAGES/messages.po
	msgfmt $^ -o $@

locales/es_ES/LC_MESSAGES/messages.mo: locales/es_ES/LC_MESSAGES/messages.po
	msgfmt $^ -o $@

locales/de_DE/LC_MESSAGES/messages.mo: locales/de_DE/LC_MESSAGES/messages.po
	msgfmt $^ -o $@

locales/it_IT/LC_MESSAGES/messages.mo: locales/it_IT/LC_MESSAGES/messages.po
	msgfmt $^ -o $@
