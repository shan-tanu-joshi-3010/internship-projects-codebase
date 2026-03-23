from opensearchpy import OpenSearch
from faker import Faker
from datetime import datetime
import random
import uuid
import time

# ----------------------------
# OpenSearch connection
# ----------------------------
client = OpenSearch(
    hosts=[{"host": "localhost", "port": 9200}],
    http_auth=("admin", "admin"),
    use_ssl=False,
    verify_certs=False
)

INDEX_NAME = "license-usage"

# ----------------------------
# Software & Feature Design
# ----------------------------

SOFTWARES = {
    "AutoCAD": ["Drafting", "Rendering", "3DModeling", "Simulation", "ExportTools"],
    "SolidWorks": ["PartDesign", "Assembly", "FlowSim", "MotionStudy", "SheetMetal"],
    "ANSYS": ["Mechanical", "Fluent", "CFX", "Thermal", "Structural"],
    "MATLAB": ["Simulink", "Optimization", "SignalProc", "ControlSys", "Statistics"],
    "ArcGIS": ["Mapping", "SpatialAnalysis", "GeoCoding", "Routing", "3DMaps"],
    "Catia": ["SurfaceDesign", "PartDesign", "Assembly", "Simulation", "Drafting"],
    "Maya": ["Animation", "Rendering", "Rigging", "Modeling", "Texturing"],
    "SAP": ["Finance", "HR", "Logistics", "Analytics", "Reporting"],
    "OracleDB": ["Backup", "Replication", "Security", "Monitoring", "Tuning"],
    "Photoshop": ["Editing", "Layers", "Filters", "Export", "AIEnhance"]
}

fake = Faker()

WORKSTATIONS = [
    "WS-101", "WS-102", "WS-103",
    "WS-104", "WS-105", "WS-106",
    "WS-107", "WS-108"
]

# ----------------------------
# Create index if not exists
# ----------------------------
if not client.indices.exists(index=INDEX_NAME):
    client.indices.create(
        index=INDEX_NAME,
        body={
            "settings": {"number_of_shards": 1},
            "mappings": {
                "properties": {
                    "processed_time": {"type": "date"},
                    "software_name": {"type": "keyword"},
                    "feature_name": {"type": "keyword"},
                    "total_license": {"type": "integer"},
                    "used_license": {"type": "integer"},
                    "user_list": {"type": "keyword"},
                    "workstation_list": {"type": "keyword"}
                }
            }
        }
    )
    print("✅ Index created")

# ----------------------------
# Generate fixed capacities
# ----------------------------

# Fixed total license per software-feature
CAPACITY_MAP = {}

for software, features in SOFTWARES.items():
    for feature in features:
        CAPACITY_MAP[(software, feature)] = random.randint(5, 10)

print("✅ Capacity map initialized")

# ----------------------------
# Real-time streaming loop
# ----------------------------

print("🚀 Starting multi-software real-time stream (every 2 minutes)")
print("🛑 Press Ctrl+C to stop\n")

try:
    while True:
        snapshot_time = datetime.utcnow().isoformat() + "Z"

        for software, features in SOFTWARES.items():
            for feature in features:

                total_license = CAPACITY_MAP[(software, feature)]
                used_license = random.randint(0, total_license)

                users = [fake.user_name() for _ in range(used_license)]
                workstation_count = min(used_license, len(WORKSTATIONS))
                workstations = random.sample(WORKSTATIONS, workstation_count)

                doc = {
                    "processed_time": snapshot_time,
                    "software_name": software,
                    "feature_name": feature,
                    "total_license": total_license,
                    "used_license": used_license,
                    "user_list": users,
                    "workstation_list": workstations
                }

                client.index(
                    index=INDEX_NAME,
                    id=str(uuid.uuid4()),
                    body=doc
                )

        print(f"📥 Inserted full system snapshot at {snapshot_time}")
        time.sleep(120)

except KeyboardInterrupt:
    print("\n🛑 Streaming stopped by user")
