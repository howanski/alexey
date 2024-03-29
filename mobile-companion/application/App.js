import React, { useState, useEffect } from "react";
import {
    Text,
    View,
    ScrollView,
    StyleSheet,
    Pressable,
    TextInput,
} from "react-native";
import AsyncStorage from "@react-native-async-storage/async-storage";
import { BarCodeScanner } from "expo-barcode-scanner";
import { activateKeepAwake, deactivateKeepAwake } from "expo-keep-awake";
import * as Linking from "expo-linking";

export default function App() {
    const [accessToken, setAccessToken] = useState(false);
    const [defaultPath, setDefaultPath] = useState(false);
    const [freezeScanner, setFreezeScanner] = useState(false);
    const [hasCameraPermission, setHasCameraPermision] = useState(null);
    const [lastResponseCode, setLastResponseCode] = useState(0);
    const [needsScan, setNeedsScan] = useState(true);
    const [responseUi, setResponseUi] = useState([]);
    const [serverUri, setServerUri] = useState(false);
    const [userLocale, setUserLocale] = useState("en");
    const [tmpServerUri, setTmpServerUri] = useState(false);
    const [timeoutId, setTimeoutId] = useState(false);
    const [transactionInProgress, setTransactionInProgress] = useState(false);
    const [settingsOpened, setSettingsOpened] = useState(false);
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

    const persistUserLocale = async (value) => {
        if (value) {
            await AsyncStorage.setItem("userLocale", value);
            setUserLocale(value);
        }
    };

    const retrieveUserLocale = async () => {
        const value = await AsyncStorage.getItem("userLocale");
        if (value) {
            setUserLocale(value);
        }
    };

    const setupRescanNeeded = async () => {
        setNeedsScan(!(defaultPath && serverUri && accessToken));
    };

    const logout = () => {
        persistServerUri("");
        persistDefaultPath("");
        persistToken("");
        setSettingsOpened(false);
    };

    const toggleSettings = () => {
        setTmpServerUri(serverUri);
        setSettingsOpened(!settingsOpened);
    };

    const saveSettings = () => {
        persistServerUri(tmpServerUri);
        setSettingsOpened(false);
    };

    const fetchPath = async (path) => {
        if (!serverUri) {
            return;
        }
        if (!accessToken) {
            return;
        }
        if (timeoutId) {
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
                        activateKeepAwake();
                    } else {
                        deactivateKeepAwake();
                    }
                    if (jsonResponse.loc && jsonResponse.loc != userLocale) {
                        persistUserLocale(jsonResponse.loc);
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
                if (obj.accessToken && !accessToken) {
                    persistToken(obj.accessToken);
                }
                if (obj.serverUri && !serverUri) {
                    persistServerUri(obj.serverUri);
                }
                if (obj.defaultPath && !defaultPath) {
                    persistDefaultPath(obj.defaultPath);
                }
                setResponseUi([]);
                setLastResponseCode(0);
            } catch {
                console.log("Scanner failed");
            }
        }
    };

    retrieveToken();
    retrieveServerUri();
    retrieveDefaultPath();
    retrieveUserLocale();
    if (lastResponseCode === 0 && serverUri && defaultPath && accessToken) {
        setLastResponseCode(999);
        fetchDefaultPath();
    }

    if (settingsOpened) {
        return (
            <View style={styles.container}>
                <ScrollView
                    style={styles.scrollContainer}
                    contentContainerStyle={styles.scrollContainerParent}
                >
                    <Text style={styles.textResponse}></Text>
                    <Text style={styles.textResponse}>--- {strings[userLocale].settings} ---</Text>
                    <Text style={styles.textResponse}></Text>
                    <Text style={styles.textResponse}>{strings[userLocale].serverUri}:</Text>
                    <TextInput
                        style={styles.input}
                        value={tmpServerUri}
                        onChangeText={setTmpServerUri}
                    />
                    <Text style={styles.textResponse}></Text>
                    <Text style={styles.textResponse}></Text>
                    <Pressable style={styles.button} onPress={logout}>
                        <Text style={styles.buttonText}>{strings[userLocale].logoutNow}</Text>
                    </Pressable>
                </ScrollView>
                <Pressable style={styles.button} onPress={saveSettings}>
                    <Text style={styles.buttonText}>
                        {strings[userLocale].save}
                    </Text>
                </Pressable>
                <Pressable style={styles.button} onPress={toggleSettings}>
                    <Text style={styles.buttonText}>{strings[userLocale].cancel}</Text>
                </Pressable>
            </View>
        );
    }

    if (needsScan) {
        if (hasCameraPermission === null) {
            return (
                <View style={styles.container}>
                    <Text style={styles.textResponse}></Text>
                </View>
            );
        }
        if (hasCameraPermission === false) {
            return (
                <View style={styles.container}>
                    <Text style={styles.textRed}>{strings[userLocale].noCameraAccess}</Text>
                </View>
            );
        }
        return (
            <View style={styles.container}>
                <BarCodeScanner
                    onBarCodeScanned={handleBarCodeScanned}
                    style={StyleSheet.absoluteFillObject}
                />
                <Text style={styles.textResponse}></Text>
            </View>
        );
    }
    return (
        <View style={styles.container}>
            {transactionInProgress && (
                <Text style={styles.textCorner}>{"<--->"}</Text>
            )}
            {!transactionInProgress && <Text style={styles.textCorner}></Text>}
            <ScrollView
                style={styles.scrollContainer}
                contentContainerStyle={styles.scrollContainerParent}
            >
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
                        } else if (uiElem.type === "lnk") {
                            return (
                                <Pressable
                                    key={prop}
                                    style={styles.buttonBlue}
                                    onPress={() => Linking.openURL(uiElem.path)}
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
            </ScrollView>
            <Pressable style={styles.button} onPress={fetchDefaultPath}>
                <Text style={styles.buttonText}>{strings[userLocale].home}</Text>
            </Pressable>
            <Pressable style={styles.button} onPress={toggleSettings}>
                <Text style={styles.buttonText}>{strings[userLocale].settings}</Text>
            </Pressable>
        </View>
    );
}

const strings = {
    en: {
        cancel: "Cancel",
        home: "Home",
        logoutNow: "Logout",
        noCameraAccess: "No access to camera :(",
        save: "Save",
        settings: "Settings",
        serverUri: "Server URI",
    },
    pl: {
        cancel: "Anuluj",
        home: "Menu główne",
        logoutNow: "Wyloguj",
        noCameraAccess: "Brak dostępu do aparatu :(",
        save: "Zapisz",
        settings: "Ustawienia",
        serverUri: "Adres serwera",
    },
};

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: "#434c5e",
        alignItems: "center",
        justifyContent: "center",
    },
    scrollContainer: {
        flex: 1,
        backgroundColor: "#434c5e",
    },
    scrollContainerParent: {
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
    buttonBlue: {
        alignItems: "center",
        justifyContent: "center",
        paddingVertical: 12,
        paddingHorizontal: 32,
        margin: 5,
        borderRadius: 4,
        elevation: 3,
        backgroundColor: "#5e81ac",
    },
    buttonText: {
        fontSize: 16,
        lineHeight: 21,
        fontWeight: "bold",
        letterSpacing: 0.25,
        color: "white",
    },
    input: {
        color: "#d8dee9",
        height: 50,
        width: "100%",
        borderColor: "gray",
        borderWidth: 1,
    },
});
