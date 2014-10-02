Little BIC extension
====================

This extension tries to make generic bank data like name, BIC code, and national bank ID accessible for CiviCRM. The information itself will be downloaded from the various national sources, and can be updated any time.

The extension also offers an API lookup service.

The information will be stored in a new option group.

Contextual Information
----------------------

Just a few months ago, 34 countries have adopted SEPA, a new iniciative that, according to [Wikipedia](http://en.wikipedia.org/wiki/Single_Euro_Payments_Area) has the aim to "improve the efficiency of cross-border payments and turn the fragmented national markets for euro payments into a single domestic one."

About one month ago, CiviSEPA was announced in this blog for the first time. The title of the post was [Free Direct Debit for Europe!](https://civicrm.org/blogs/bjoerne/free-direct-debit-europe) and, in fact, it was already pointing one of the key things about SEPA: to adopt it is expensive and complex.

We, the CiviCRM Community, are lucky, because we have a free (not only as in free beer but also as in free speech) extension that allows us to manage Direct Debit transactions with banks all around the SEPA area.

Working with SEPA During the Edale Sprint
-----------------------------------------

These days, a few members of the CiviCRM community, including implementors, administrator and users, have been working together in a [sprint](http://en.wikipedia.org/wiki/Sprint_%28software_development%29). Different groups have been created, to cover different new features, review extensions, documentate projects and, in general, think and improve CiviCRM.

One of this groups have been talking about how to improve CiviSEPA to make it more generic, flexible and easy to use. This post, actually, is one of the products of the discussions that have been taking place into this workgroup.

What Are BIC Codes?
-------------------

The main idea of the SEPA project is to facilitate the way we make bank transactions, so procedures and warranties are (almost) the same all over the countries that adopted SEPA. From now on, we'll be asked for our IBAN -our "personal" bank account number- and our bank's BIC code.

BIC codes are just strings, like TRIOESMMXXX, that works as identifiers for institutions (financial institutions in our case). Since SEPA was introduced, we are supposed to ask and be asked for IBAN and BIC codes, when working with direct debit, EFT, etc.

By the way, you may found BIC codes named in a lot of different ways: SWIFT code, SWIFT-BIC, SWIFT id, etc. For more information, take a look at the [ISO 9362](http://en.wikipedia.org/wiki/ISO_9362) documentation.

Why to Keep a List of BIC Codes in CiviCRM?
-------------------------------------------

When a supporter is filling a form, or a user is using CiviCRM to introduce information about a SEPA transaction, both fields are going to be waiting to be filled. But, here is the trick, a lot of times, you can obtain the corresponding BIC code by just looking at the IBAN. So, wouldn't it be nice to see how BIC codes are auto-filled, when possible?

The Society for Worldwide Interbank Financial Telecommunication (SWIFT) is the organization that manages SWIFT codes. SWIFT partners can actually have access to the complete list of codes, but it costs 2,500€ per year to be a partner.

Nevertheless, some of the institutions that are responsible of the SEPA implementation in different countries, are making available its corresponding list of entities. In Germany, you can download the list of German BIC codes from the [Deutsche Bundesbank website](http://www.bundesbank.de/Redaktion/DE/Standardartikel/Aufgaben/Unbarer_Zahlungsverkehr/bankleitzahlen_download.html). In Spain, you can download the list of Spanish BIC codes from the [Banco de España website](http://www.bde.es/bde/es/secciones/servicios/Particulares_y_e/Registros_de_Ent/).

That made us think that, if we created an extension that downloaded this information and updated it into CiviCRM, we would be avoiding legal problems and, at the same time, letting CiviCRM users freely work with BIC codes.

So... if we agree that having a list of BIC codes updated in CiviCRM can be practical... and we've found a viable way to do it... then... let's go do it!

How to Keep the List of Banks Updated?
--------------------------------------

According to the philosophy of making the CiviSEPA improvements as generic as possible, we've been thinking about creating an extension that allows CiviCRM users to keep autoupdated a list of banks, with it's corresponding BIC codes.

This new extension (Björn and I like to call it the "little BIC extension"), will create an option group called bank_list. When you install the extension, this list will be created but empty. Then, the extension will offer you the possibility of automatically fill this list by downloading information from the corresponding institution (Bundesbank, Banco de España, etc).

> P.S.: Actually, we've already created the skeleton for the extension in GitHub, so if you know how to programatically obtain the list of BIC codes for any other country from an official source, your contribution will be more than welcome!
