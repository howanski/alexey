import React, { useState, useEffect } from "react";
import { Text, View, StyleSheet, Button, Pressable } from "react-native";
import AsyncStorage from "@react-native-async-storage/async-storage";
import { BarCodeScanner } from "expo-barcode-scanner";

export default function App() {
    const [accessToken, setAccessToken] = useState(false);
    const [defaultPath, setDefaultPath] = useState(false);
    const [freezeScanner, setFreezeScanner] = useState(false);
    const [hasCemeraPermission, setHasCameraPermision] = useState(null);
    const [lastResponseCode, setLastResponseCode] = useState(0);
    const [needsScan, setNeedsScan] = useState(true);
    const [responseUi, setResponseUi] = useState([]);
    const [serverUri, setServerUri] = useState(false);
    const [timeoutId, setTimeoutId] = useState(false);
    const [transactionInProgress, setTransactionInProgress] = useState(false);
    useEffect(() => {
        (async () => {
            const { status } = await BarCodeScanner.requestPermissionsAsync();
            setHasCameraPermision(status === "granted");
        })();
    }, []);

    const persistToken = async (value) => {
        await AsyncStorage.setItem("accessToken", value);
        setAccessToken(value);
        setupRescanNeeded();
    };

    const retrieveToken = async () => {
        const value = await AsyncStorage.getItem("accessToken");
        setAccessToken(value);
        setupRescanNeeded();
    };

    const persistServerUri = async (value) => {
        await AsyncStorage.setItem("serverUri", value);
        setServerUri(value);
        setupRescanNeeded();
    };

    const retrieveServerUri = async () => {
        const value = await AsyncStorage.getItem("serverUri");
        setServerUri(value);
        setupRescanNeeded();
    };

    const persistDefaultPath = async (value) => {
        await AsyncStorage.setItem("defaultPath", value);
        setDefaultPath(value);
        setupRescanNeeded();
    };

    const retrieveDefaultPath = async () => {
        const value = await AsyncStorage.getItem("defaultPath");
        setDefaultPath(value);
        setupRescanNeeded();
    };

    const setupRescanNeeded = async () => {
        setNeedsScan(!(defaultPath && serverUri && accessToken));
    };

    const logout = () => {
        persistToken("");
        persistServerUri("");
        persistDefaultPath("");
    };

    const fetchPath = async (path) => {
        if (timeoutId){
            clearTimeout(timeoutId);
            setTimeoutId(false);
        }
        let fullPath = serverUri + path;
        if (fullPath && accessToken) {
            setFreezeScanner(true);
            setTransactionInProgress(true);
            fetch(fullPath, {
                method: "GET",
                headers: { "X-ALEXEY-SECRET": accessToken },
            })
                .then((response) => response.json())
                .then((jsonResponse) => {
                    if (jsonResponse.code === 401) {
                        persistToken("");
                        persistServerUri("");
                        persistDefaultPath("");
                    }
                    if (jsonResponse.ui) {
                        setResponseUi(jsonResponse.ui);
                    } else {
                        setResponseUi([]);
                    }
                    if (
                        jsonResponse.autoRefresh &&
                        jsonResponse.autoRefresh > 0
                    ) {
                        let tmId = setTimeout(() => {
                            fetchPath(path);
                        }, jsonResponse.autoRefresh);
                        setTimeoutId(tmId);
                    }
                    setLastResponseCode(jsonResponse.code);
                    setFreezeScanner(false);
                    setTransactionInProgress(false);
                })
                .catch((error) => {
                    console.error(error);
                    console.log("END OF RESPONSE ERROR");
                    setFreezeScanner(false);
                    setTransactionInProgress(false);
                });
        }
    };

    const fetchDefaultPath = () => {
        fetchPath(defaultPath);
    };

    const handleBarCodeScanned = ({ type, data }) => {
        if (!freezeScanner) {
            try {
                let obj = JSON.parse(data);
                if (obj.accessToken) {
                    persistToken(obj.accessToken);
                }
                if (obj.serverUri) {
                    persistServerUri(obj.serverUri);
                }
                if (obj.defaultPath) {
                    persistDefaultPath(obj.defaultPath);
                }
                fetchDefaultPath();
            } catch {
                console.log("Scanner failed");
            }
        }
    };

    retrieveToken();
    retrieveServerUri();
    retrieveDefaultPath();
    if (lastResponseCode === 0) {
        setLastResponseCode(999);
        fetchDefaultPath();
    }

    if (needsScan) {
        if (hasCemeraPermission === null) {
            return (
                <View style={styles.container}>
                    <Text style={styles.textResponse}>
                        Requesting for camera permission...
                    </Text>
                </View>
            );
        }
        if (hasCemeraPermission === false) {
            return (
                <View style={styles.container}>
                    <Text style={styles.textRed}>No access to camera</Text>
                </View>
            );
        }
        return (
            <View style={styles.container}>
                <BarCodeScanner
                    onBarCodeScanned={handleBarCodeScanned}
                    style={StyleSheet.absoluteFillObject}
                />
                <Text style={styles.textResponse}>... SCAN QR ...</Text>
            </View>
        );
    }
    return (
        <View style={styles.container}>
            {transactionInProgress && (
                <Text style={styles.textCorner}>...</Text>
            )}
            {!transactionInProgress && <Text style={styles.textCorner}></Text>}

            {responseUi.map((uiElem, prop) => {
                if (uiElem) {
                    if (uiElem.type === "txt") {
                        return (
                            <Text style={styles.textResponse} key={prop}>
                                {uiElem.value}
                            </Text>
                        );
                    } else if (uiElem.type === "btn") {
                        return (
                            <Pressable
                                key={prop}
                                style={styles.button}
                                onPress={() => fetchPath(uiElem.path)}
                            >
                                <Text style={styles.buttonText}>
                                    {uiElem.name}
                                </Text>
                            </Pressable>
                        );
                    } else {
                        console.log(uiElem);
                        console.log("Unknown elem type " + uiElem.type);
                    }
                }
            })}
            <Pressable style={styles.button} onPress={fetchDefaultPath}>
                <Text style={styles.buttonText}>Home</Text>
            </Pressable>
            <Pressable style={styles.button} onPress={logout}>
                <Text style={styles.buttonText}>Logout</Text>
            </Pressable>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: "#434c5e",
        alignItems: "center",
        justifyContent: "center",
    },
    textRed: {
        color: "#bf616a",
        fontSize: 42,
    },
    textResponse: {
        color: "#d8dee9",
        fontSize: 25,
    },
    textCorner: {
        color: "#d8dee9",
        fontSize: 25,
        textAlignVertical: "top",
    },
    button: {
        alignItems: "center",
        justifyContent: "center",
        paddingVertical: 12,
        paddingHorizontal: 32,
        margin: 5,
        borderRadius: 4,
        elevation: 3,
        backgroundColor: "black",
    },
    buttonText: {
        fontSize: 16,
        lineHeight: 21,
        fontWeight: "bold",
        letterSpacing: 0.25,
        color: "white",
    },
    input: {
        color: "#cf0a3b",
        height: 40,
        borderColor: "gray",
        borderWidth: 1,
    },
});
