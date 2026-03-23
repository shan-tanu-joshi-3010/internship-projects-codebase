# analytics_service/entity_extractor.py

SOFTWARE_MAP = {
    "AUTOCAD": "AutoCAD",
    "SOLIDWORKS": "SolidWorks",
    "ANSYS": "ANSYS",
    "MATLAB": "MATLAB",
    "ARCGIS": "ArcGIS",
    "CATIA": "Catia",
    "MAYA": "Maya",
    "SAP": "SAP",
    "ORACLEDB": "OracleDB",
    "PHOTOSHOP": "Photoshop"
}

FEATURE_MAP = {
    "DRAFTING": "Drafting",
    "RENDERING": "Rendering",
    "3DMODELING": "3DModeling",
    "SIMULATION": "Simulation",
    "EXPORTTOOLS": "ExportTools",
    "PARTDESIGN": "PartDesign",
    "ASSEMBLY": "Assembly",
    "FLOWSIM": "FlowSim",
    "MOTIONSTUDY": "MotionStudy",
    "SHEETMETAL": "SheetMetal",
    "MECHANICAL": "Mechanical",
    "FLUENT": "Fluent",
    "CFX": "CFX",
    "THERMAL": "Thermal",
    "STRUCTURAL": "Structural",
    "SIMULINK": "Simulink",
    "OPTIMIZATION": "Optimization",
    "SIGNALPROC": "SignalProc",
    "CONTROLSYS": "ControlSys",
    "STATISTICS": "Statistics",
    "MAPPING": "Mapping",
    "SPATIALANALYSIS": "SpatialAnalysis",
    "GEOCODING": "GeoCoding",
    "ROUTING": "Routing",
    "3DMAPS": "3DMaps",
    "SURFACEDESIGN": "SurfaceDesign",
    "ANIMATION": "Animation",
    "RIGGING": "Rigging",
    "MODELING": "Modeling",
    "TEXTURING": "Texturing",
    "FINANCE": "Finance",
    "HR": "HR",
    "LOGISTICS": "Logistics",
    "ANALYTICS": "Analytics",
    "REPORTING": "Reporting",
    "BACKUP": "Backup",
    "REPLICATION": "Replication",
    "SECURITY": "Security",
    "MONITORING": "Monitoring",
    "TUNING": "Tuning",
    "EDITING": "Editing",
    "LAYERS": "Layers",
    "FILTERS": "Filters",
    "EXPORT": "Export",
    "AIENHANCE": "AIEnhance"
}


def extract_entities(question: str):
    question_upper = question.upper()

    detected_software = []
    detected_features = []

    for key, value in SOFTWARE_MAP.items():
        if key in question_upper:
            detected_software.append(value)

    for key, value in FEATURE_MAP.items():
        if key in question_upper:
            detected_features.append(value)

    return detected_software, detected_features
