// Sample Collection Item for testing
const SAMPLE_COLLECTION_ITEM = {
    "BatchId": "00000000-0000-0000-0000-000000000000",
    "CollectionId": "356257f5-8dfe-468f-849f-93d9a3d2422f",
    "PaymentScheduleItemIds": [
        "eb737109-a04b-4dd3-bd7b-1e6f0f71c1cb",
        "ef3aba95-37a0-49a1-ac84-6e9d2327b9cc"
    ],
    "PolicyNumber": "OUTINT00172379",
    "RiskId": 1,
    "RiskCode": "VEH",
    "RiskMajorVersion": 1,
    "IsRealtime": true,
    "IsResubmission": false,
    "ResubmissionId": null,
    "AmountDue": 108.53,
    "DueDate": "2024-08-08",
    "ValueDate": "2024-08-08",
    "CollectionStatus": "Collected",
    "IsLatest": true,
    "Sequence": 1,
    "PaymentMethod": {
        "$type": "CardPaymentMethodDocument",
        "Token": "1854393882611111",
        "GatewayReference": "OUTINT00172379-AUTH-20240808084249",
        "MaskedCardNumber": "411111******1111",
        "NameOnCard": "CROOKS",
        "CardType": "VISA CREDIT",
        "ExpiryDate": "08/2026",
        "PaymentProvider": "Boipa"
    },
    "TransactionReference": "OUTINT00172379-1-1-VEH-1",
    "OriginalTransactionReference": null,
    "ProviderDetails": {
        "ProcessingDate": "2024-08-08T08:43:07.260869+00:00",
        "Filename": null,
        "ErrorCode": null,
        "ErrorMessage": null
    },
    "id": "Collection-1-1",
    "_etag": "\"d304d0b2-0000-0c00-0000-67ebdb990000\"",
    "CreatedBy": "SYSTEM",
    "CreatedDate": "2024-08-08T08:43:07.5350326+00:00",
    "ModifiedBy": "DatabaseUpdater",
    "ModifiedDate": "2025-04-01T12:27:05.7976906+00:00",
    "_rid": "f9tgAIE9ikd8+gEAAAAAAA==",
    "_self": "dbs/f9tgAA==/colls/f9tgAIE9ikc=/docs/f9tgAIE9ikd8+gEAAAAAAA==/",
    "_attachments": "attachments/",
    "CollectionFrequency": "Monthly",
    "SourceSystem": "PolicyAdmin",
    "_ts": 1743510425
};

// DOM elements
const collectionItemInput = document.getElementById('collectionItemInput');
const shadowLedgerOutput = document.getElementById('shadowLedgerOutput');
const convertBtn = document.getElementById('convertBtn');
const clearBtn = document.getElementById('clearBtn');
const loadSampleBtn = document.getElementById('loadSampleBtn');
const copyBtn = document.getElementById('copyBtn');
const statusBadge = document.getElementById('statusBadge');
const requestType = document.getElementById('requestType');
const errorSection = document.getElementById('errorSection');
const errorMessage = document.getElementById('errorMessage');

// Utility functions
function updateStatus(status, message, type = '') {
    statusBadge.textContent = message;
    statusBadge.className = `status-badge ${status}`;
    
    if (type) {
        requestType.textContent = `${type} Request`;
        requestType.style.display = 'inline-block';
    } else {
        requestType.style.display = 'none';
    }
}

function showError(error) {
    errorMessage.textContent = error;
    errorSection.style.display = 'block';
    updateStatus('error', 'Error');
}

function hideError() {
    errorSection.style.display = 'none';
}

function formatDate(dateString) {
    if (!dateString) return null;
    
    // Handle ISO datetime strings - extract just the date part
    if (dateString.includes('T')) {
        return dateString.split('T')[0];
    }
    
    return dateString;
}

function formatDateTime(dateString) {
    if (!dateString) return null;
    
    // If it's already in ISO format, return as is
    if (dateString.includes('T') && dateString.includes('Z')) {
        return dateString;
    }
    
    // If it's a date only, convert to ISO datetime
    if (!dateString.includes('T')) {
        return `${dateString}T00:00:00.000Z`;
    }
    
    return dateString;
}

function determineRequestType(collectionStatus) {
    const raiseStatuses = ['created', 'refunded', 'collected'];
    const failedStatuses = ['rejected'];

    const normalizedStatus = collectionStatus?.trim().toLowerCase();

    if (raiseStatuses.includes(normalizedStatus)) {
        return 'raise';
    } else if (failedStatuses.includes(normalizedStatus)) {
        return 'failed';
    } else {
        throw new Error(`Unknown CollectionStatus: ${collectionStatus}. Expected one of: ${[...raiseStatuses, ...failedStatuses].join(', ')}`);
    }
}

function determineDirectDebitFlag(paymentMethod) {
    if (!paymentMethod || !paymentMethod['$type']) {
        return true; // Default to true for non-card payments
    }
    
    return paymentMethod['$type'] !== 'CardPaymentMethodDocument';
}

function convertCollectionItem(collectionItem) {
    // Validate required fields
    const requiredFields = [
        'CollectionId', 'PolicyNumber', 'RiskCode', 'RiskMajorVersion',
        'ValueDate', 'RiskId', 'TransactionReference', 'CollectionStatus',
        'PaymentScheduleItemIds'
    ];
    
    for (const field of requiredFields) {
        if (collectionItem[field] === undefined || collectionItem[field] === null) {
            throw new Error(`Missing required field: ${field}`);
        }
    }
    
    // Determine request type based on CollectionStatus
    const requestType = determineRequestType(collectionItem.CollectionStatus);
    
    // Determine if it's a direct debit payment
    const isDirectDebitPayment = determineDirectDebitFlag(collectionItem.PaymentMethod);
    
    // Get processing date for transaction/reported date
    const processingDate = collectionItem.ProviderDetails?.ProcessingDate;
    if (!processingDate) {
        throw new Error('Missing ProviderDetails.ProcessingDate field');
    }
    
    // Base request object
    const baseRequest = {
        policyNumber: collectionItem.PolicyNumber,
        riskCode: collectionItem.RiskCode,
        riskMajorVersion: collectionItem.RiskMajorVersion,
        valueDate: formatDate(collectionItem.ValueDate),
        riskId: collectionItem.RiskId,
        paymentScheduleId: collectionItem.CollectionId,
        transactionReference: collectionItem.TransactionReference,
        isDirectDebitPayment: isDirectDebitPayment,
        collectionItemId: collectionItem.id || collectionItem.CollectionId,
        paymentScheduleItemIds: collectionItem.PaymentScheduleItemIds
    };
    
    // Add type-specific date field
    if (requestType === 'raise') {
        baseRequest.transactionDate = formatDateTime(processingDate);
    } else if (requestType === 'failed') {
        baseRequest.reportedDate = formatDate(processingDate);
    }
    
    return {
        request: baseRequest,
        type: requestType
    };
}

// Event handlers
function handleConvert() {
    try {
        hideError();
        
        const inputText = collectionItemInput.value.trim();
        if (!inputText) {
            throw new Error('Please enter a Collection Item JSON');
        }
        
        // Parse JSON
        let collectionItem;
        try {
            collectionItem = JSON.parse(inputText);
        } catch (parseError) {
            throw new Error(`Invalid JSON format: ${parseError.message}`);
        }
        
        // Convert to Shadow Ledger request
        const result = convertCollectionItem(collectionItem);
        
        // Display result
        shadowLedgerOutput.value = JSON.stringify(result.request, null, 2);
        updateStatus('success', 'Converted Successfully', result.type.toUpperCase());
        
        // Add success animation
        shadowLedgerOutput.parentElement.classList.add('success-animation');
        setTimeout(() => {
            shadowLedgerOutput.parentElement.classList.remove('success-animation');
        }, 600);
        
    } catch (error) {
        showError(error.message);
        shadowLedgerOutput.value = '';
    }
}

function handleClear() {
    collectionItemInput.value = '';
    shadowLedgerOutput.value = '';
    hideError();
    updateStatus('ready', 'Ready');
}

function handleLoadSample() {
    collectionItemInput.value = JSON.stringify(SAMPLE_COLLECTION_ITEM, null, 2);
    hideError();
    updateStatus('ready', 'Sample Loaded');
}

async function handleCopy() {
    try {
        const text = shadowLedgerOutput.value;
        if (!text) {
            throw new Error('Nothing to copy');
        }
        
        await navigator.clipboard.writeText(text);
        
        // Visual feedback
        const originalText = copyBtn.innerHTML;
        copyBtn.innerHTML = '✓';
        copyBtn.style.background = '#27ae60';
        
        setTimeout(() => {
            copyBtn.innerHTML = originalText;
            copyBtn.style.background = '#3498db';
        }, 1000);
        
    } catch (error) {
        console.error('Failed to copy:', error);
        // Fallback for older browsers
        shadowLedgerOutput.select();
        document.execCommand('copy');
    }
}

// Event listeners
convertBtn.addEventListener('click', handleConvert);
clearBtn.addEventListener('click', handleClear);
loadSampleBtn.addEventListener('click', handleLoadSample);
copyBtn.addEventListener('click', handleCopy);

// Keyboard shortcuts
collectionItemInput.addEventListener('keydown', (e) => {
    if (e.ctrlKey && e.key === 'Enter') {
        e.preventDefault();
        handleConvert();
    }
});

// Auto-resize textareas
function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
}

collectionItemInput.addEventListener('input', () => {
    autoResize(collectionItemInput);
    hideError();
    updateStatus('ready', 'Ready');
});

shadowLedgerOutput.addEventListener('input', () => {
    autoResize(shadowLedgerOutput);
});

// Initialize
updateStatus('ready', 'Ready');

// Add some helpful validation messages
const validationHelpers = {
    validateCollectionStatus: (status) => {
        const validStatuses = ['Created', 'Refunded', 'Collected', 'Rejected'];
        if (!validStatuses.includes(status)) {
            return `Invalid CollectionStatus: "${status}". Valid values are: ${validStatuses.join(', ')}`;
        }
        return null;
    },
    
    validatePaymentMethod: (paymentMethod) => {
        if (!paymentMethod) {
            return 'PaymentMethod object is missing';
        }
        if (!paymentMethod['$type']) {
            return 'PaymentMethod.$type field is missing';
        }
        return null;
    }
};

// Export for testing (if in module environment)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        convertCollectionItem,
        determineRequestType,
        determineDirectDebitFlag,
        formatDate,
        formatDateTime,
        validationHelpers
    };
}
