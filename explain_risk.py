import pandas as pd
import shap
import joblib
import sys
import numpy as np

import os

BASE_DIR = os.path.dirname(os.path.abspath(__file__))

model = joblib.load(os.path.join(BASE_DIR, "license_risk_model.pkl"))
le = joblib.load(os.path.join(BASE_DIR, "label_encoder.pkl"))

# Input features
data = {
    "total_licenses": int(sys.argv[1]),
    "used_licenses": int(sys.argv[2]),
    "unused_percent": float(sys.argv[3]),
    "days_to_expiry": int(sys.argv[4]),
    "avg_usage_30d": int(sys.argv[5])
}
FEATURE_MEANING = {
    "used_licenses": "low number of actively used licenses",
    "unused_percent": "high percentage of unused licenses",
    "total_licenses": "large total license allocation",
    "days_to_expiry": "license expiry approaching soon",
    "avg_usage_30d": "low average daily usage"
}


X = pd.DataFrame([data])

# Predict
pred = model.predict(X)[0]
risk_label = le.inverse_transform([pred])[0]

# SHAP explanation
explainer = shap.TreeExplainer(model)
shap_values = explainer.shap_values(X)

# ---- FINAL ROBUST SHAP HANDLING ----
shap_array = np.array(shap_values)

if shap_array.ndim == 3:
    shap_vals = np.mean(np.abs(shap_array), axis=(0, 1))
elif shap_array.ndim == 2:
    shap_vals = np.mean(np.abs(shap_array), axis=0)
else:
    raise ValueError("Unexpected SHAP output shape")

# Sort features by importance
feature_importance = list(zip(X.columns, shap_vals))
sorted_features = sorted(
    feature_importance,
    key=lambda x: float(x[1]),
    reverse=True
)

# Output
print(risk_label)

top_features = sorted_features[:3]

reasons = []
for feature, value in top_features:
    if feature in FEATURE_MEANING:
        reasons.append(FEATURE_MEANING[feature])

# Build natural language explanation
if risk_label == "CRITICAL":
    explanation = (
        "This software is marked CRITICAL mainly due to "
        + " and ".join(reasons) + "."
    )
elif risk_label == "COMPLIANCE_RISK":
    explanation = (
        "This software faces compliance risk because of "
        + " and ".join(reasons) + "."
    )
elif risk_label == "WASTAGE_RISK":
    explanation = (
        "This software shows wastage risk due to "
        + " and ".join(reasons) + "."
    )
else:
    explanation = (
        "This software is operating within safe limits with no major risk indicators."
    )

print("EXPLANATION_TEXT:" + explanation)

# Still print SHAP details (for transparency)
for feature, value in top_features:
    print(f"{feature}:{round(float(value),3)}")
