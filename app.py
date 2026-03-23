from fastapi import FastAPI
from analytics_service.opensearch_client import get_client
from analytics_service.router import execute
from analytics_service.validator import validate_query_object
from analytics_service.entity_extractor import extract_entities
import re

app = FastAPI()
client = get_client()

@app.post("/analyze")
def analyze(query_object: dict):

    print("\n========== BACKEND RECEIVED ==========")
    print(query_object)
    print("======================================\n")

    if not isinstance(query_object, dict):
        query_object = {}

    # ---- Defaults ----
    query_object.setdefault("intent", "current")
    query_object.setdefault("metric", "latest")
    query_object.setdefault("software", [])
    query_object.setdefault("feature", [])
    query_object.setdefault("time_range", {"type": "relative", "value": "7d"})
    query_object.setdefault("aggregation_scope", "business_hours")
    query_object.setdefault("group_by", "none")
    query_object.setdefault("comparison", False)
    query_object.setdefault("filters", {})
    query_object.setdefault("raw_question", "")

    # ---- ENTITY EXTRACTION ----
    detected_software, detected_feature = extract_entities(
        query_object["raw_question"]
    )

    if detected_software:
        query_object["software"] = detected_software

    if detected_feature:
        query_object["feature"] = detected_feature

    # ---- ABSOLUTE DATE DETECTION ----
    date_match = re.search(r"\d{4}-\d{2}-\d{2}", query_object["raw_question"])

    if date_match:
        query_object["time_range"] = {
            "type": "absolute",
            "value": date_match.group()
        }

    print("\nAFTER ENTITY + DATE INJECTION:")
    print(query_object)
    print("======================================\n")

    validated = validate_query_object(query_object)

    return execute(validated, client)
