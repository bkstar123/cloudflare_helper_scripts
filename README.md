# Cloudflare Helper Scripts

This repository provides some helper scripts to interact with Cloudflare API. At this time of writing, it offers the following capabilities:  
&ndash; Fetch the current custom SSL configuration for a list of given zones & save the output to a csv file for easier importing to Excel.   
&ndash; Upload/Replace a custom certificate for a list of given zones.  
&ndash; Fetch certificate data from the list of given URL (may/maynot on Cloudflare): certificate fingerprint, expire date, days until expiration, DNS A & CNAME record and so on. The output will be saved to a csv file for easier importing to Excel.  
&ndash; Quickly decode a certificate file and print the output to the standard output (stdout).  
&ndash; Filter all unique root/apex domains from a list of given custom domains  

## Usage

### Installation

- Firstly, download the repository to your local machine and run ```composer install``` to install all the dependent packages.  
- Rename ```.env.example``` to ```.env```, put your Cloudflare API key to ```.env``` , rename ```6.cffirewall_rule_params.php.example``` to ```6.cffirewall_rule_params.php```, and create all necessary files in the ***input*** directory (naming them after the ones you specify in the ```.env```)  

### Execution

```bash
# You should make sure that all necessary data are already put in the respective input files
php 1.cfCheckZoneCustomSSL.php
php 1.cfUploadSSLCert.php
php 3.checkSSLFromCertFile.php
php 4.checkSSLFromURL.php
php 5.filterApexRootDomain.php
php 6.cfFirewallRuleCreate.php
```
