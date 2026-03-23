import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import LabelEncoder
import joblib

# Load data
df = pd.read_csv("license_data.csv")

X = df.drop("risk", axis=1)
y = df["risk"]

# Encode target
le = LabelEncoder()
y_encoded = le.fit_transform(y)

# Train model
model = RandomForestClassifier(
    n_estimators=100,
    random_state=42
)
model.fit(X, y_encoded)

# Save model and encoder
joblib.dump(model, "license_risk_model.pkl")
joblib.dump(le, "label_encoder.pkl")

print("Model trained and saved successfully")
