using UnityEngine;
using System.Collections;
using System.Collections.Generic;
using System;
using System.Net;
using System.Net.NetworkInformation;

namespace Logging
{
	public static class Utils
	{

		/// <summary>
		/// Finds the MAC address of the NIC with maximum speed.
		/// </summary>
		/// <returns>The MAC address.</returns>
		public static string GetMacAddress()
		{

		    const int MIN_MAC_ADDR_LENGTH = 12;
		    string macAddress = string.Empty;
		    long maxSpeed = -1;

		    foreach (NetworkInterface nic in NetworkInterface.GetAllNetworkInterfaces())
		    {
		        // Debug.Log(
		        //     "Found MAC Address: " + nic.GetPhysicalAddress() +
		        //     " Type: " + nic.NetworkInterfaceType);

		        string tempMac = nic.GetPhysicalAddress().ToString();
		        if (nic.Speed > maxSpeed &&
		            !string.IsNullOrEmpty(tempMac) &&
		            tempMac.Length >= MIN_MAC_ADDR_LENGTH)
		        {
		            // Debug.Log("New Max Speed = " + nic.Speed + ", MAC: " + tempMac);
		            maxSpeed = nic.Speed;
		            macAddress = tempMac;
		        }
		    }

		    return macAddress;
		}

		public static String GetIP()
		{
			string strHostName = "";
			strHostName = System.Net.Dns.GetHostName();
		  IPHostEntry ipEntry = System.Net.Dns.GetHostEntry(strHostName);
			IPAddress[] addr = ipEntry.AddressList;
			return addr[addr.Length-1].ToString();
		}

		public static IEnumerator RetryConnection(WWW www, WWWForm form, int retries = 5)
		{
			do
			{
				// form.AddField("retries", retries);
				Debug.Log("Retry nr. " + retries + "\n" +
									"Url: " + www.url + "\n" + 
									"Error: " + www.error);

				www = new WWW(www.url, form);
				yield return www;
			} while(String.IsNullOrEmpty(www.error) == false && --retries >= 0);
		}

	}
}