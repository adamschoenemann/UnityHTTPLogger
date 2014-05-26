using UnityEngine;
using System.Collections;
using System.Collections.Generic;
using System;

public class LogEntry
{

	private LinkedList<KeyValuePair<string, string>> strings;
	private LinkedList<KeyValuePair<string, int>> ints;
	private LinkedList<KeyValuePair<string, float>> floats;
	private LinkedList<KeyValuePair<string, Vector3>> vector3s;
	private LinkedList<KeyValuePair<string, Quaternion>> quaternions;

	public int scene_id;
	public int session_id; // TODO: REMOVE THIS, NOT USED SINCE WE HAVE scene_id
	private float time;
	private Loggable origin;
	public string event_name { get; private set; }

	public LogEntry(Loggable origin, string event_name = "None")
	{
		this.origin = origin;
		this.event_name = event_name;
		this.time = Time.timeSinceLevelLoad;
		strings = new LinkedList<KeyValuePair<string, string>>();
		ints = new LinkedList<KeyValuePair<string, int>>();
		floats = new LinkedList<KeyValuePair<string, float>>();
		vector3s = new LinkedList<KeyValuePair<string, Vector3>>();
		quaternions = new LinkedList<KeyValuePair<string, Quaternion>>();
	}

	public LogEntry AddString(string key, string val)
	{
		strings.AddLast(new KeyValuePair<string, string>(key, val));
		return this;
	}

	public LogEntry AddInt(string key, int val)
	{
		ints.AddLast(new KeyValuePair<string, int>(key, val));
		return this;
	}

	public LogEntry AddFloat(string key, float val)
	{
		floats.AddLast(new KeyValuePair<string, float>(key, val));
		return this;
	}

	public LogEntry AddQuaternion(string key, Quaternion val)
	{
		quaternions.AddLast(new KeyValuePair<string, Quaternion>(key, val));
		return this;
	}

	public LogEntry AddVector3(string key, Vector3 val)
	{
		vector3s.AddLast(new KeyValuePair<string, Vector3>(key, val));
		return this;
	}

	public LogEntry AddGameObject(string name, GameObject go)
	{
		AddString("go_name", name);
		AddString(name + "_tag", go.tag);
		AddVector3(name + "_position", go.transform.position);
		AddQuaternion(name + "_rotation", go.transform.rotation);
		AddInt(name + "_instance_id", go.GetInstanceID());
		return this;
	}

	public void ToForm(WWWForm form, int i)
	{
		if(session_id == 0 || scene_id == 0)
		{
			Debug.Log("Logging invalid Entry!!!!!\n" +
							  "name: " + origin.name +  "\n" +
							  "loggable_id: " + origin.id +  "\n" +
							  "event: " + event_name + "\n" +
							  "scene_id: " + scene_id + "\n" +
							  "session_id: " + session_id);
			return;
		}
		string entryKey = "entries[" + i + "]";
		form.AddField(entryKey + "[session_id]", session_id);
		form.AddField(entryKey + "[scene_id]", scene_id);
		form.AddField(entryKey + "[loggable_id]", origin.id);
		form.AddField(entryKey + "[event]", event_name);
		form.AddField(entryKey + "[time]", time.ToString());

		
		PutMetaData(entryKey + "[strings]", strings, form);
		PutMetaData(entryKey + "[ints]", ints, form);
		PutMetaData(entryKey + "[floats]", floats, form);
		PutVector3(entryKey + "[vector3s]", vector3s, form);
		PutQuaternion(entryKey + "[quaternions]", quaternions, form);

		// Debug.Log("Entry converted to form");

	}

	private void
	PutQuaternion(string key, LinkedList<KeyValuePair<string, Quaternion>> list, WWWForm form)
	{
		if(list.Count > 0)
		{
			int j = 0;
			foreach(KeyValuePair<string, Quaternion> kvp in list)
			{
				string kbase = key + "[" + j + "]";
				form.AddField(kbase + "[key]", kvp.Key);
				Quaternion v = kvp.Value;
				form.AddField(kbase + "[x]", v.x.ToString());
				form.AddField(kbase + "[y]", v.y.ToString());
				form.AddField(kbase + "[z]", v.z.ToString());
				form.AddField(kbase + "[w]", v.w.ToString());
				j++;
			}
		}
	}

	private void
	PutVector3(string key, LinkedList<KeyValuePair<string, Vector3>> list, WWWForm form)
	{
		if(list.Count > 0)
		{
			int j = 0;
			foreach(KeyValuePair<string, Vector3> kvp in list)
			{
				string kbase = key + "[" + j + "]";
				form.AddField(kbase + "[key]", kvp.Key);
				Vector3 v = kvp.Value;
				form.AddField(kbase + "[x]", v.x.ToString());
				form.AddField(kbase + "[y]", v.y.ToString());
				form.AddField(kbase + "[z]", v.z.ToString());
				j++;
			}
		}
	}

	private void 
	PutMetaData<V>(string key, LinkedList<KeyValuePair<string, V>> list, WWWForm form)
	{
		if(list.Count > 0)
		{
			int j = 0;
			foreach(KeyValuePair<string, V> kvp in list)
			{
				string k = key + "[" + j + "][key]";
				form.AddField(k, kvp.Key);
				string v = key + "[" + j + "][value]";
				form.AddField(v, kvp.Value.ToString());
				j++;
			}
		}
	}

}