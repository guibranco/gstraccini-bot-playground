let currentMessageId = '';

function setStorageItem(key, value) {
    localStorage.setItem(key, value);
}

function getStorageItem(key) {
    return localStorage.getItem(key);
}

function removeStorageItem(key) {
    localStorage.removeItem(key);
}

function saveFields() {
    const oin = document.getElementById('oin').value.trim();
    const creditorName = document.getElementById('creditorName').value.trim();
    const creditorIban = document.getElementById('creditorIban').value.trim();
    const collectionDate = document.getElementById('collectionDate').value;
    const transactionType = document.getElementById('transactionType').value;

    const fieldsData = {
        oin,
        creditorName,
        creditorIban,
        collectionDate,
        transactionType
    };

    setStorageItem('sepaConverterFields', JSON.stringify(fieldsData));

    const successDiv = document.getElementById('successMessage');
    successDiv.innerHTML = '<div class="success-message">✅ Fields saved successfully! They will be restored next time you open the converter.</div>';

    setTimeout(() => {
        successDiv.innerHTML = '';
    }, 3000);
}

function loadSavedFields() {
    const savedData = getStorageItem('sepaConverterFields');

    if (savedData) {
        try {
            const fields = JSON.parse(savedData);

            if (fields.oin) document.getElementById('oin').value = fields.oin;
            if (fields.creditorName) document.getElementById('creditorName').value = fields.creditorName;
            if (fields.creditorIban) document.getElementById('creditorIban').value = fields.creditorIban;
            if (fields.collectionDate) document.getElementById('collectionDate').value = fields.collectionDate;
            if (fields.transactionType) document.getElementById('transactionType').value = fields.transactionType;

            const successDiv = document.getElementById('successMessage');
            successDiv.innerHTML = '<div class="info-message">ℹ️ Previously saved fields have been restored.</div>';

            setTimeout(() => {
                successDiv.innerHTML = '';
            }, 3000);
        } catch (error) {
            console.error('Error loading saved fields:', error);
        }
    }
}

function clearSavedFields() {
    removeStorageItem('sepaConverterFields');

    document.getElementById('oin').value = '';
    document.getElementById('creditorName').value = '';
    document.getElementById('creditorIban').value = '';
    document.getElementById('collectionDate').value = getDefaultCollectionDate();
    document.getElementById('transactionType').value = 'NORMAL';

    const successDiv = document.getElementById('successMessage');
    successDiv.innerHTML = '<div class="info-message">🗑️ Saved fields cleared and form reset.</div>';

    setTimeout(() => {
        successDiv.innerHTML = '';
    }, 3000);
}

function formatDate(dateString) {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        return date.toISOString().split('T')[0];
    } catch (e) {
        return dateString;
    }
}

function formatDateTime(date = null) {
    const now = date || new Date();
    return now.toISOString().replace(/\.\d{3}Z$/, '');
}

function generateMessageId(transactionType = 'NORMAL') {
    const now = new Date();
    const dateStr = now.toISOString().split('T')[0].replace(/-/g, '');
    const timeStr = now.toTimeString().split(' ')[0].replace(/:/g, '');
    return `${dateStr}-${timeStr}-${transactionType}-PAIN008`;
}

function getSequenceType(isFirstDirectDebit) {
    return isFirstDirectDebit ? 'FRST' : 'RCUR';
}

function getDefaultCollectionDate() {
    const date = new Date();
    date.setDate(date.getDate() + 2);
    return date.toISOString().split('T')[0];
}

function getCollectionDate() {
    const collectionDateInput = document.getElementById('collectionDate');
    return collectionDateInput.value || getDefaultCollectionDate();
}

function cleanIban(iban) {
    return iban ? iban.trim().replace(/\s+/g, '') : '';
}

function initializeCollectionDate() {
    const collectionDateInput = document.getElementById('collectionDate');
    if (!collectionDateInput.value) {
        collectionDateInput.value = getDefaultCollectionDate();
    }
}

function convertToXML() {
    const jsonInput = document.getElementById('jsonInput').value.trim();
    const oin = document.getElementById('oin').value.trim();
    const creditorName = document.getElementById('creditorName').value.trim();
    const creditorIban = document.getElementById('creditorIban').value.trim();
    const transactionType = document.getElementById('transactionType').value;
    const errorDiv = document.getElementById('errorMessage');
    const successDiv = document.getElementById('successMessage');
    const xmlOutput = document.getElementById('xmlOutput');
    const copyBtn = document.getElementById('copyBtn');

    errorDiv.innerHTML = '';
    successDiv.innerHTML = '';

    if (!jsonInput) {
        errorDiv.innerHTML = '<div class="error-message">Please enter JSON data</div>';
        return;
    }

    if (!oin) {
        errorDiv.innerHTML = '<div class="error-message">Please enter an OIN</div>';
        return;
    }

    if (!creditorName) {
        errorDiv.innerHTML = '<div class="error-message">Please enter a creditor account name</div>';
        return;
    }

    if (!creditorIban) {
        errorDiv.innerHTML = '<div class="error-message">Please enter a creditor IBAN</div>';
        return;
    }

    try {
        const data = JSON.parse(jsonInput);

        const requiredFields = ['AmountDue', 'Iban', 'AccountHolderName', 'MandateId', 'MandateSignedDate'];
        const missingFields = requiredFields.filter(field => !data[field]);

        if (missingFields.length > 0) {
            throw new Error(`Missing required fields: ${missingFields.join(', ')}`);
        }

        const msgId = generateMessageId(transactionType);
        currentMessageId = msgId;
        const seqType = getSequenceType(data.IsFirstDirectDebit);
        const collectionDate = getCollectionDate();
        const createdDateTime = formatDateTime();
        const cleanedIban = cleanIban(data.Iban);
        const cleanedCreditorIban = cleanIban(creditorIban);
        const endToEndId = data.TransactionReference || data.id || 'NOTPROVIDED';

        const xml = `<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<Document
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.001.08"
>
  <CstmrDrctDbtInitn>
    <GrpHdr>
      <MsgId>${msgId}</MsgId>
      <CreDtTm>${createdDateTime}</CreDtTm>
      <NbOfTxs>1</NbOfTxs>
      <CtrlSum>${data.AmountDue}</CtrlSum>
      <InitgPty>
        <Id>
          <PrvtId>
            <Othr>
              <Id>${oin}</Id>
            </Othr>
          </PrvtId>
        </Id>
      </InitgPty>
    </GrpHdr>
    <PmtInf>
      <PmtInfId>${msgId}-${seqType}</PmtInfId>
      <PmtMtd>DD</PmtMtd>
      <NbOfTxs>1</NbOfTxs>
      <CtrlSum>${data.AmountDue}</CtrlSum>
      <PmtTpInf>
        <SvcLvl>
          <Cd>SEPA</Cd>
        </SvcLvl>
        <LclInstrm>
          <Cd>CORE</Cd>
        </LclInstrm>
        <SeqTp>${seqType}</SeqTp>
      </PmtTpInf>
      <ReqdColltnDt>${collectionDate}</ReqdColltnDt>
      <Cdtr>
        <Nm>${creditorName}</Nm>
      </Cdtr>
      <CdtrAcct>
        <Id>
          <IBAN>${cleanedCreditorIban}</IBAN>
        </Id>
      </CdtrAcct>
      <CdtrAgt>
        <FinInstnId>
          <Othr>
            <Id>NOTPROVIDED</Id>
          </Othr>
        </FinInstnId>
      </CdtrAgt>
      <CdtrSchmeId>
        <Id>
          <PrvtId>
            <Othr>
              <Id>${oin}</Id>
              <SchmeNm>
                <Prtry>SEPA</Prtry>
              </SchmeNm>
            </Othr>
          </PrvtId>
        </Id>
      </CdtrSchmeId>
      <DrctDbtTxInf>
        <PmtId>
          <EndToEndId>${endToEndId}</EndToEndId>
        </PmtId>
        <InstdAmt Ccy="EUR">${data.AmountDue}</InstdAmt>
        <DrctDbtTx>
          <MndtRltdInf>
            <MndtId>${data.MandateId}</MndtId>
            <DtOfSgntr>${formatDate(data.MandateSignedDate)}</DtOfSgntr>
          </MndtRltdInf>
        </DrctDbtTx>
        <DbtrAgt>
          <FinInstnId>
            <Othr>
              <Id>NOTPROVIDED</Id>
            </Othr>
          </FinInstnId>
        </DbtrAgt>
        <Dbtr>
          <Nm>${data.AccountHolderName}</Nm>
        </Dbtr>
        <DbtrAcct>
          <Id>
            <IBAN>${cleanedIban}</IBAN>
          </Id>
        </DbtrAcct>
      </DrctDbtTxInf>
    </PmtInf>
  </CstmrDrctDbtInitn>
</Document>`;

        xmlOutput.value = xml;
        copyBtn.style.display = 'block';
        document.getElementById('downloadBtn').style.display = 'block';
        successDiv.innerHTML = '<div class="success-message">✅ JSON successfully converted to SEPA XML format!</div>';

    } catch (error) {
        errorDiv.innerHTML = `<div class="error-message">Error: ${error.message}</div>`;
        xmlOutput.value = '';
        copyBtn.style.display = 'none';
        document.getElementById('downloadBtn').style.display = 'none';
    }
}

function copyToClipboard() {
    const xmlOutput = document.getElementById('xmlOutput');
    xmlOutput.select();
    document.execCommand('copy');

    const copyBtn = document.getElementById('copyBtn');
    const originalText = copyBtn.textContent;
    copyBtn.textContent = 'Copied!';

    setTimeout(() => {
        copyBtn.textContent = originalText;
    }, 2000);
}

function downloadXML() {
    const xmlOutput = document.getElementById('xmlOutput');
    const xmlContent = xmlOutput.value;

    if (!xmlContent) {
        alert('No XML content to download');
        return;
    }

    const filename = currentMessageId ? `${currentMessageId}.xml` : 'sepa-payment.xml';

    const blob = new Blob([xmlContent], { type: 'application/xml' });
    const url = window.URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);

    const downloadBtn = document.getElementById('downloadBtn');
    const originalText = downloadBtn.textContent;
    downloadBtn.textContent = 'Downloaded!';

    setTimeout(() => {
        downloadBtn.textContent = originalText;
    }, 2000);
}

function setupAutoSave() {
    const fields = ['oin', 'creditorName', 'creditorIban', 'collectionDate', 'transactionType'];

    fields.forEach(fieldId => {
        const element = document.getElementById(fieldId);
        element.addEventListener('change', () => {
            clearTimeout(element.autoSaveTimeout);
            element.autoSaveTimeout = setTimeout(() => {
                if (element.value.trim()) {
                    saveFields();
                }
            }, 1000);
        });
    });
}

window.addEventListener('load', function () {
    initializeCollectionDate();
    loadSavedFields();
    setupAutoSave();
    const jsonInput = document.getElementById('jsonInput');
    if (jsonInput.value.trim()) {
        convertToXML();
    }
});
